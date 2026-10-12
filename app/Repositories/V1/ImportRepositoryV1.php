<?php

namespace App\Repositories\V1;

use App\Enums\V1\ImportStatus;
use App\Models\V1\Import;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ImportRepositoryV1
{
    private $id, $user_id, $original_name, $file_path, $status, $total_rows,
        $processed_rows, $success_rows, $failed_rows, $batch_id, $error_message,
        $started_at, $completed_at, $created_at;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    public function setOriginalName($original_name)
    {
        $this->original_name = $original_name;
        return $this;
    }

    public function setFilePath($file_path)
    {
        $this->file_path = $file_path;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setTotalRows($total_rows)
    {
        $this->total_rows = $total_rows;
        return $this;
    }

    public function setProcessedRows($processed_rows)
    {
        $this->processed_rows = $processed_rows;
        return $this;
    }

    public function setSuccessRows($success_rows)
    {
        $this->success_rows = $success_rows;
        return $this;
    }

    public function setFailedRows($failed_rows)
    {
        $this->failed_rows = $failed_rows;
        return $this;
    }

    public function setBatchId($batch_id)
    {
        $this->batch_id = $batch_id;
        return $this;
    }

    public function setErrorMessage($error_message)
    {
        $this->error_message = $error_message;
        return $this;
    }

    public function setStartedAt($started_at)
    {
        $this->started_at = $started_at;
        return $this;
    }

    public function setCompletedAt($completed_at)
    {
        $this->completed_at = $completed_at;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function store(): Import
    {
        return Import::create([
            'user_id' => $this->user_id,
            'original_name' => $this->original_name,
            'file_path' => $this->file_path,
            'status' => ImportStatus::Pending,
        ]);
    }

    public function findById(): ?object
    {
        return $this->baseQuery()->where('id', $this->id)->first();
    }

    public function paginateAll(int $perPage): LengthAwarePaginator
    {
        return DB::table('imports as i')
            ->join('users as u', 'u.id', '=', 'i.user_id')
            ->whereNull('i.deleted_at')
            ->select(
                'i.id',
                'i.original_name',
                'i.status',
                'i.total_rows',
                'i.processed_rows',
                'i.success_rows',
                'i.failed_rows',
                'i.created_at',
                'i.completed_at',
                'u.name as uploaded_by',
            )
            ->orderByDesc('i.created_at')
            ->orderByDesc('i.id')
            ->paginate($perPage);
    }

    public function markProcessing(): int
    {
        return Import::whereKey($this->id)->update([
            'status' => ImportStatus::Processing,
            'total_rows' => $this->total_rows,
            'started_at' => now(),
        ]);
    }
    
    public function incrementCounters(): int
    {
        return Import::whereKey($this->id)->incrementEach([
            'processed_rows' => $this->processed_rows,
            'success_rows' => $this->success_rows,
            'failed_rows' => $this->failed_rows,
        ]);
    }

    public function markFinished(): int
    {
        return Import::whereKey($this->id)->update([
            'status' => $this->status,
            'error_message' => $this->error_message,
            'completed_at' => now(),
        ]);
    }

    private function baseQuery(): Builder
    {
        return DB::table('imports')
            ->whereNull('deleted_at')
            ->select(
                'id',
                'user_id',
                'original_name',
                'file_path',
                'status',
                'total_rows',
                'processed_rows',
                'success_rows',
                'failed_rows',
                'error_message',
                'started_at',
                'completed_at',
                'created_at',
            );
    }
}
