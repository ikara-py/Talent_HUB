<?php

namespace App\Models;

class Application
{
    private int $id;
    private string $candidateName;
    private string $cvPath;
    private string $status;

    public function __construct(
        int $id,
        string $candidateName,
        string $cvPath,
        string $status
    ) {
        $this->id = $id;
        $this->candidateName = $candidateName;
        $this->cvPath = $cvPath;
        $this->status = $status;
    }

    public function getCandidateName(): string { return $this->candidateName; }
    public function getCvPath(): string { return $this->cvPath; }
    public function getStatus(): string { return $this->status; }
}
