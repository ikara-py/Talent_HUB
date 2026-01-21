<?php

namespace App\Models;

class Candidate extends User
{
    private string $phone;
    private array $tags;
    private string $created_at;

    public function __construct(
        string $firstName,
        string $lastName,
        string $username,
        string $email,
        string $password,
        Role $role,
        string $phone,
        array $tags,
        string $created_at
    ) {
        parent::__construct(
            $firstName,
            $lastName,
            $username,
            $email,
            $password,
            $role
        );

        $this->phone = $phone;
        $this->tags = $tags;
        $this->created_at = $created_at;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }
}
