<?php

namespace App\Models;

class JobOffer
{
    private ?int $id;
    private string $title;
    private string $description;
    private int $recruiterId;
    private int $categoryId;
    private bool $isArchived;

    public function __construct(
        ?int $id,
        string $title,
        string $description,
        int $recruiterId,
        bool $isArchived = false,
        int $categoryId = 1
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->recruiterId = $recruiterId;
        $this->isArchived = $isArchived;
        $this->categoryId = $categoryId;
    }

    public function getId(): ?int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getRecruiterId(): int { return $this->recruiterId; }
    public function getCategoryId(): int { return $this->categoryId; }
    public function isArchived(): bool { return $this->isArchived; }
}
