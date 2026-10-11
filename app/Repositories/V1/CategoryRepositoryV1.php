<?php

namespace App\Repositories\V1;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CategoryRepositoryV1
{
    private $id, $name, $slug, $description, $is_active, $created_at;

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

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getActive(): Collection
    {
        return DB::table('categories')
            ->select('id', 'name')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();
    }

    public function getNameById(): ?string
    {
        return DB::table('categories')->where('id', $this->id)->value('name');
    }
}
