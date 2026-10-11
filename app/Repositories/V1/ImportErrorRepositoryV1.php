<?php

namespace App\Repositories\V1;

class ImportErrorRepositoryV1
{
    private $id, $import_id, $row_number, $row_data, $errors, $created_at;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setImportId($import_id)
    {
        $this->import_id = $import_id;
        return $this;
    }

    public function setRowNumber($row_number)
    {
        $this->row_number = $row_number;
        return $this;
    }

    public function setRowData($row_data)
    {
        $this->row_data = $row_data;
        return $this;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }
}
