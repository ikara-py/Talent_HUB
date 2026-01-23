<?php

namespace App\Models;

class JobOffer
{
    private ?int $id;
    private string $title;
    private string $description;
    private int $recruiterId;
    private bool $isArchived;

    public function __construct(
        ?int $id,
        string $title,
        string $description,
        int $recruiterId,
        bool $isArchived = false
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->recruiterId = $recruiterId;
        $this->isArchived = $isArchived;
    }

    public function getId(): ?int { 
        return $this->id; 
    }

    public function getTitle(): string { 
        return $this->title; 
    }

    public function getDescription(): string { 
        return $this->description; 
    }

    public function getRecruiterId(): int { 
        return $this->recruiterId; 
    }

    public function isArchived(): bool { 
        return $this->isArchived; 
    }
}
