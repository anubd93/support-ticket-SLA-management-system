<?php

namespace App\Services\V1\Import;

use Generator;
use SplFileObject;

class TicketCsvReader
{
    public const COLUMNS = ['external_reference', 'subject', 'description', 'priority', 'category', 'customer_email', 'agent_email'];

    public const REQUIRED_COLUMNS = ['external_reference', 'subject', 'description', 'priority', 'category', 'customer_email'];

    private array $header;

    public function __construct(private readonly string $path)
    {
        $this->header = $this->readHeader();
    }

    public function missingColumns(): array
    {
        return array_values(array_diff(self::REQUIRED_COLUMNS, $this->header));
    }

    public function countRows(): int
    {
        $count = 0;

        foreach ($this->records() as $values) {
            $count++;
        }

        return max(0, $count - 1);
    }

    public function rows(int $skip = 0): Generator
    {
        $rowNumber = 0;

        foreach ($this->records() as $values) {
            $rowNumber++;
            
            if ($rowNumber === 1 || $rowNumber - 1 <= $skip) {
                continue;
            }

            $row = [];

            foreach (self::COLUMNS as $column) {
                $index = array_search($column, $this->header, true);
                $row[$column] = $index === false ? '' : trim((string) ($values[$index] ?? ''));
            }

            yield $rowNumber => $row;
        }
    }

    private function readHeader(): array
    {
        foreach ($this->records() as $values) {
            $values[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $values[0]);

            return array_map(fn ($name) => strtolower(trim((string) $name)), $values);
        }

        return [];
    }

    private function records(): Generator
    {
        $file = new SplFileObject($this->path, 'r');

        while (! $file->eof()) {
            $values = $file->fgetcsv(',', '"', '');

            if ($values === false || $values === [null]) {
                continue;
            }

            yield $values;
        }
    }
}
