<?php

namespace App\Repositories\V1;

class UserRepositoryV1
{
    private $id, $role_id, $name, $email, $email_verified_at, $password,
        $is_active, $is_available, $last_assigned_at, $created_at;

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function setRoleId($role_id)
    {
        $this->role_id = $role_id;
        return $this;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function setEmailVerifiedAt($email_verified_at)
    {
        $this->email_verified_at = $email_verified_at;
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;
        return $this;
    }

    public function setIsAvailable($is_available)
    {
        $this->is_available = $is_available;
        return $this;
    }

    public function setLastAssignedAt($last_assigned_at)
    {
        $this->last_assigned_at = $last_assigned_at;
        return $this;
    }

    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }
}
