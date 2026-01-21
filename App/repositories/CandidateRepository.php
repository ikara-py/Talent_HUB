<?php

namespace App\Repositories;

use App\Models\Candidate;
use App\Models\Role;
use Database;
use PDO;

class CandidateRepository extends UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findById(int $id): ?Candidate
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM candidates WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToCandidate($data);
    }

    public function findByEmail(string $email): ?Candidate
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM candidates WHERE email = :email"
        );
        $stmt->execute(['email' => $email]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToCandidate($data);
    }

    private function mapToCandidate(array $data): Candidate
    {
        return new Candidate(
            $data['first_name'],
            $data['last_name'],
            $data['username'],
            $data['email'],
            $data['password'],
            new Role($data['role']),
            $data['phone'] ?? '',
            isset($data['tags']) ? explode(',', $data['tags']) : [],
            $data['created_at']
        );
    }
}
