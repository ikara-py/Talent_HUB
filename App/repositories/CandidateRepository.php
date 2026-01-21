<?php
namespace App\Repositories;
use App\Models\Candidate;
use Database;
use PDO;

class CandidateRepository extends UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findById(int $id): Candidate
    {
        $stmt = $this->db->prepare("SELECT * FROM candidates WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Candidate(
            $data['phone'] ?? '',
            isset($data['tags']) ? explode(',', $data['tags']) : [],
            $data['created_at']
        );
    }

    public function findByEmail(string $email): Candidate
    {
        $stmt = $this->db->prepare("SELECT * FROM candidates WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Candidate(
            $data['phone'] ?? '',
            isset($data['tags']) ? explode(',', $data['tags']) : [],
            $data['created_at']
        );
    }
}
