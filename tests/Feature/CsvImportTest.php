<?php

namespace Tests\Feature;

use App\Enums\V1\ImportStatus;
use App\Enums\V1\TicketSource;
use App\Jobs\V1\ProcessTicketImportJob;
use App\Models\User;
use App\Models\V1\Import;
use App\Models\V1\ImportError;
use App\Models\V1\Ticket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * CSV import with validation, invalid rows, duplicates and error reporting.
 * Files up to tickets.import.sync_max_rows rows are imported in the request; bigger ones are queued.
 */
#[Group('import')]
class CsvImportTest extends TestCase
{
    private const HEADER = 'external_reference,subject,description,priority,category,customer_email,agent_email';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->admin = $this->admin();
        User::factory()->customer()->create(['email' => 'anna@example.com']);
        User::factory()->customer()->create(['email' => 'ben@example.com']);
    }

    private function upload(array $rows, ?User $as = null): TestResponse
    {
        $csv = implode("\n", [self::HEADER, ...$rows]) . "\n";

        return $this->actingAs($as ?? $this->admin)
            ->withHeader('Accept', 'application/json')
            ->post(route('imports.store'), [
                'file' => UploadedFile::fake()->createWithContent('tickets.csv', $csv),
            ]);
    }

    public function test_valid_rows_become_tickets(): void
    {
        $this->upload([
            'CRM-1,Cannot log in,Error 500 after the OTP,high,Technical,anna@example.com,',
            'CRM-2,Refund request,"Charged twice, please refund",medium,billing,BEN@example.com,',
        ])->assertCreated();

        $import = Import::firstOrFail();
        $this->assertSame(ImportStatus::Completed, $import->status);
        $this->assertSame(2, $import->success_rows);
        $this->assertSame(0, $import->failed_rows);

        $ticket = Ticket::where('external_reference', 'CRM-1')->firstOrFail();
        $this->assertSame(TicketSource::Import, $ticket->source);
        $this->assertSame('anna@example.com', $ticket->creator->email);
        $this->assertNotNull($ticket->ticket_number);
        $this->assertNotNull($ticket->sla_due_at);
    }

    public function test_invalid_rows_are_reported_and_valid_rows_still_imported(): void
    {
        $this->upload([
            'CRM-10,Good row,Fine,low,Billing,anna@example.com,',
            'CRM-11,Bad priority,Text,urgent,Billing,anna@example.com,',
            'CRM-12,Unknown customer,Text,low,Billing,nobody@example.com,',
            'CRM-13,Unknown category,Text,low,Space travel,anna@example.com,',
            'CRM-10,Duplicate in file,Text,low,Billing,anna@example.com,',
        ])->assertCreated();

        $import = Import::firstOrFail();
        $this->assertSame(ImportStatus::CompletedWithErrors, $import->status);
        $this->assertSame(1, $import->success_rows);
        $this->assertSame(4, $import->failed_rows);

        // Row numbers count the header as row 1, so the second data row is row 3.
        $errors = ImportError::where('import_id', $import->id)->orderBy('row_number')->get();
        $this->assertSame([3, 4, 5, 6], $errors->pluck('row_number')->all());
        $this->assertArrayHasKey('priority', $errors[0]->errors);
        $this->assertArrayHasKey('customer_email', $errors[1]->errors);
        $this->assertArrayHasKey('category', $errors[2]->errors);
        $this->assertArrayHasKey('external_reference', $errors[3]->errors);
        $this->assertSame(1, Ticket::count());
    }

    public function test_uploading_the_same_file_again_imports_nothing_twice(): void
    {
        $rows = ['CRM-20,Printer offline,Office printer,low,Technical,anna@example.com,'];

        $this->upload($rows)->assertCreated();
        $this->upload($rows)->assertCreated();

        $second = Import::latest('id')->firstOrFail();
        $this->assertSame(0, $second->success_rows);
        $this->assertSame(1, $second->failed_rows);
        $this->assertStringContainsString('Already imported', ImportError::where('import_id', $second->id)->first()->errors['external_reference'][0]);
        $this->assertSame(1, Ticket::where('external_reference', 'CRM-20')->count());
    }

    public function test_a_file_without_required_columns_fails_clearly(): void
    {
        $this->actingAs($this->admin)
            ->withHeader('Accept', 'application/json')
            ->post(route('imports.store'), [
                'file' => UploadedFile::fake()->createWithContent('tickets.csv', "subject,priority\nHello,low\n"),
            ])
            ->assertCreated();

        $import = Import::firstOrFail();
        $this->assertSame(ImportStatus::Failed, $import->status);
        $this->assertStringContainsString('external_reference', $import->error_message);
    }

    public function test_large_files_are_processed_by_a_queued_job(): void
    {
        Queue::fake();
        config(['tickets.import.sync_max_rows' => 1]);

        $this->upload([
            'CRM-30,One,Text,low,Billing,anna@example.com,',
            'CRM-31,Two,Text,low,Billing,ben@example.com,',
        ])->assertCreated();

        Queue::assertPushedOn('imports', ProcessTicketImportJob::class);
        $this->assertSame(ImportStatus::Pending, Import::firstOrFail()->status);
        $this->assertSame(0, Ticket::count());
    }

    public function test_only_files_with_csv_content_are_accepted(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('imports.store'), ['file' => UploadedFile::fake()->create('photo.png', 10, 'image/png')])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    public function test_only_admins_can_import(): void
    {
        $this->upload(['CRM-40,One,Text,low,Billing,anna@example.com,'], $this->agent())->assertForbidden();

        $this->assertSame(0, Import::count());
    }
}
