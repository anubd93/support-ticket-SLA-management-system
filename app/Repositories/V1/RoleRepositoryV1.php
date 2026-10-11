<?php

namespace App\Repositories\V1;

class RoleRepositoryV1
{
    private $id, $name, $display_name, $description, $created_at;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setDisplayName($display_name)
    {
        $this->display_name = $display_name;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }
}
