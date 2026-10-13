<?php

namespace App\Services\V1;

use App\Enums\V1\ImportStatus;
use App\Enums\V1\TicketPriority;
use App\Enums\V1\UserRole;
use App\Jobs\V1\ProcessTicketImportJob;
use App\Models\User;
use App\Repositories\V1\CategoryRepositoryV1;
use App\Repositories\V1\ImportErrorRepositoryV1;
use App\Repositories\V1\ImportRepositoryV1;
use App\Repositories\V1\TicketRepositoryV1;
use App\Repositories\V1\UserRepositoryV1;
use App\Services\V1\Import\TicketCsvReader;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ImportServiceV1
{
    private const ERRORS_PER_PAGE = 25;

    public function __construct(private readonly ImportRepositoryV1 $importRepositoryV1,
                                private readonly ImportErrorRepositoryV1 $importErrorRepositoryV1,
                                private readonly TicketRepositoryV1 $ticketRepositoryV1,
                                private readonly UserRepositoryV1 $userRepositoryV1,
                                private readonly CategoryRepositoryV1 $categoryRepositoryV1,
                                private readonly TicketServiceV1 $ticketServiceV1
    ) {}

    public function start(UploadedFile $file, User $user): object
    {
        $path = $file->storeAs('imports', Str::uuid() . '.csv', config('tickets.import.disk'));

        $import = $this->importRepositoryV1
            ->setUserId($user->id)
            ->setOriginalName(Str::limit(basename($file->getClientOriginalName()), 250, ''))
            ->setFilePath($path)
            ->store();

        $rows = (new TicketCsvReader(Storage::disk(config('tickets.import.disk'))->path($path)))->countRows();

        if ($rows > config('tickets.import.sync_max_rows')) {
            ProcessTicketImportJob::dispatch($import->id);

            return $import;
        }
        try {
            ProcessTicketImportJob::dispatchSync($import->id);
        } catch (Throwable) {}

        return $import;
    }
    
    public function process(int $importId): void
    {
        $import = $this->importRepositoryV1->setId($importId)->findById();

        if (! $import || ImportStatus::from($import->status)->isFinished()) {
            return;
        }

        $reader = new TicketCsvReader(Storage::disk(config('tickets.import.disk'))->path($import->file_path));

        if ($missing = $reader->missingColumns()) {
            $this->finish($import, ImportStatus::Failed, 'Missing required column(s): ' . implode(', ', $missing) . '.');

            return;
        }
        
        if ($import->status === ImportStatus::Pending->value) {
            $this->importRepositoryV1->setId($importId)->setTotalRows($reader->countRows())->markProcessing();
        }

        // Category by name, any case: [lowercased name => id], loaded once per import.
        $categories = $this->categoryRepositoryV1->getActive()
            ->mapWithKeys(fn ($category) => [strtolower($category->name) => (int) $category->id])
            ->all();
        $chunkSize = (int) config('tickets.import.chunk');
        $seen = [];
        $chunk = [];
        
        foreach ($reader->rows((int) $import->processed_rows) as $rowNumber => $row) {
            $chunk[$rowNumber] = $row;

            if (count($chunk) === $chunkSize) {
                $this->processChunk($import, $chunk, $categories, $seen);
                $chunk = [];
            }
        }

        if ($chunk) {
            $this->processChunk($import, $chunk, $categories, $seen);
        }

        $done = $this->importRepositoryV1->setId($importId)->findById();
        $this->finish($done, $done->failed_rows > 0 ? ImportStatus::CompletedWithErrors : ImportStatus::Completed);
    }
    
    public function markFailed(int $importId, string $message): void
    {
        $import = $this->importRepositoryV1->setId($importId)->findById();

        if ($import) {
            $this->finish($import, ImportStatus::Failed, Str::limit($message, 500));
        }
    }

    public function getImports(): LengthAwarePaginator
    {
        return $this->importRepositoryV1->paginateAll(config('tickets.per_page'));
    }

    public function findImport(int $id): ?object
    {
        return $this->importRepositoryV1->setId($id)->findById();
    }

    public function getErrors(int $importId): LengthAwarePaginator
    {
        return $this->importErrorRepositoryV1->setImportId($importId)->paginateByImport(self::ERRORS_PER_PAGE);
    }
    
    public function template(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, TicketCsvReader::COLUMNS, ',', '"', '');
            fputcsv($out, ['CRM-10001', 'Cannot log in', 'Error 500 after entering the OTP code.', 'high', 'Technical', 'user_01@test.com', ''], ',', '"', '');
            fputcsv($out, ['CRM-10002', 'Refund request', "Charged twice for March.\nPlease refund one payment.", 'medium', 'Billing', 'user_02@test.com', 'support_agent_01@supportdesk.com'], ',', '"', '');
            fputcsv($out, ['CRM-10003', 'Change account email', 'Please update my login email address.', 'low', 'Account', 'user_03@test.com', ''], ',', '"', '');
            fclose($out);
        }, 'ticket-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
    
    private function processChunk(object $import, array $rows, array $categories, array &$seen): void
    {
        DB::transaction(function () use ($import, $rows, $categories, &$seen) {
            $existing = $this->ticketRepositoryV1->getExistingExternalReferences(array_column($rows, 'external_reference'));
            $customers = $this->userRepositoryV1->getActiveIdsByEmail(array_column($rows, 'customer_email'), UserRole::Customer);
            $agents = $this->userRepositoryV1->getActiveIdsByEmail(array_column($rows, 'agent_email'), UserRole::Agent);

            $success = 0;
            $failed = 0;

            foreach ($rows as $rowNumber => $row) {
                $errors = $this->validateRow($row, $categories, $customers, $agents, $existing, $seen);

                if (! $errors) {
                    try {
                        $this->ticketServiceV1->createFromImport(
                            ['category_id' => $categories[strtolower($row['category'])]] + $row,
                            $customers[strtolower($row['customer_email'])],
                            $row['agent_email'] !== '' ? $agents[strtolower($row['agent_email'])] : null,
                            (int) $import->user_id
                        );

                        $seen[$row['external_reference']] = true;
                        $success++;

                        continue;
                    } catch (Throwable $exception) {
                        $errors = ['row' => ['This row could not be saved: ' . Str::limit($exception->getMessage(), 200)]];
                    }
                }

                $this->importErrorRepositoryV1
                    ->setImportId($import->id)
                    ->setRowNumber($rowNumber)
                    ->setRowData($row)
                    ->setErrors($errors)
                    ->store();

                $failed++;
            }

            $this->importRepositoryV1
                ->setId($import->id)
                ->setProcessedRows(count($rows))
                ->setSuccessRows($success)
                ->setFailedRows($failed)
                ->incrementCounters();
        });
    }

    private function validateRow(array $row, array $categories, array $customers, array $agents, array $existing, array $seen): array
    {
        $errors = Validator::make($row, [
            'external_reference' => ['required', 'string', 'max:100'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'category' => ['required', 'string'],
            'customer_email' => ['required', 'email'],
            'agent_email' => ['nullable', 'email'],
        ])->errors()->toArray();

        $reference = $row['external_reference'];

        if (! isset($errors['external_reference'])) {
            if (isset($existing[$reference])) {
                $errors['external_reference'][] = "Already imported as {$existing[$reference]}.";
            } elseif (isset($seen[$reference])) {
                $errors['external_reference'][] = 'Duplicate external_reference earlier in this file.';
            }
        }

        if (! isset($errors['category']) && ! isset($categories[strtolower($row['category'])])) {
            $errors['category'][] = "Unknown or inactive category \"{$row['category']}\".";
        }

        if (! isset($errors['customer_email']) && ! isset($customers[strtolower($row['customer_email'])])) {
            $errors['customer_email'][] = 'No active customer with this email.';
        }

        if ($row['agent_email'] !== '' && ! isset($errors['agent_email']) && ! isset($agents[strtolower($row['agent_email'])])) {
            $errors['agent_email'][] = 'No active support agent with this email.';
        }

        return $errors;
    }

    private function finish(object $import, ImportStatus $status, ?string $message = null): void
    {
        $this->importRepositoryV1
            ->setId($import->id)
            ->setStatus($status)
            ->setErrorMessage($message)
            ->markFinished();
    }
}
