<?php

namespace App\Models;

class Recruiter extends User
{
    private ?int $companyId;

    public function __construct(
        string $firstName,
        string $lastName,
        string $username,
        string $email,
        string $password,
        Role $role,
        ?int $companyId = null
    ) {
        parent::__construct(
            $firstName,
            $lastName,
            $username,
            $email,
            $password,
            $role
        );

        $this->companyId = $companyId;
    }

    public function getCompanyId(): ?int
    {
        return $this->companyId;
    }

    public function setCompanyId(?int $companyId): void
    {
        $this->companyId = $companyId;
    }
}
