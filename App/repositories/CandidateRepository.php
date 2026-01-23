<?php

namespace App\Repositories;

use App\Models\Candidate;
use App\Models\Role;
use App\Models\Tag;
use Database;
use PDO;

class CandidateRepository
{
    private PDO $db;
    private RoleRepository $roleRepository;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->roleRepository = new RoleRepository();
    }

    public function save(Candidate $candidate): bool
    {
        try {
            $this->db->beginTransaction();
            $userSql = "INSERT INTO users (first_name, last_name, username, email, password, role_id) 
                        VALUES (:first_name, :last_name, :username, :email, :password, :role_id)";
            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute([
                'first_name' => $candidate->getFirstName(),
                'last_name'  => $candidate->getLastName(),
                'username'   => $candidate->getUsername(),
                'email'      => $candidate->getEmail(),
                'password'   => $candidate->getPassword(),
                'role_id'    => $candidate->getRole()->getId()
            ]);

            $userId = $this->db->lastInsertId();

            $candidateSql = "INSERT INTO candidates (user_id, cv_path, expected_salary) 
                            VALUES (:user_id, :cv_path, :expected_salary)";
            $candidateStmt = $this->db->prepare($candidateSql);
            $candidateStmt->execute([
                'user_id'         => $userId,
                'cv_path'         => $candidate->getCvPath(),
                'expected_salary' => $candidate->getExpectedSalary()
            ]);

            if (!empty($candidate->getTags())) {
                $this->saveTags($userId, $candidate->getTags());
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function findByUserId(int $userId): ?Candidate
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, candidates.cv_path, candidates.expected_salary 
                FROM users 
                INNER JOIN candidates ON users.id = candidates.user_id 
                WHERE users.id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $role = $this->roleRepository->findById((int)$row['role_id']);
        $tags = $this->getCandidateTags($userId);

        return new Candidate(
            (int)$row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['username'],
            $row['email'],
            $row['password'],
            $role,
            $row['cv_path'],
            $row['expected_salary'] ? (float)$row['expected_salary'] : null,
            $tags
        );
    }

    public function findByEmail(string $email): ?Candidate
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, candidates.cv_path, candidates.expected_salary 
                FROM users 
                INNER JOIN candidates ON users.id = candidates.user_id 
                WHERE users.email = :email";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $role = $this->roleRepository->findById((int)$row['role_id']);
        $tags = $this->getCandidateTags((int)$row['id']);

        return new Candidate(
            (int)$row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['username'],
            $row['email'],
            $row['password'],
            $role,
            $row['cv_path'],
            $row['expected_salary'] ? (float)$row['expected_salary'] : null,
            $tags
        );
    }

    public function update(Candidate $candidate): bool
    {
        try {
            $this->db->beginTransaction();

            $userSql = "UPDATE users 
                       SET first_name = :first_name, 
                           last_name = :last_name, 
                           username = :username, 
                           email = :email 
                       WHERE id = :user_id";

            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute([
                'first_name' => $candidate->getFirstName(),
                'last_name'  => $candidate->getLastName(),
                'username'   => $candidate->getUsername(),
                'email'      => $candidate->getEmail(),
                'user_id'    => $candidate->getUserId()
            ]);

            $candidateSql = "UPDATE candidates 
                            SET cv_path = :cv_path, 
                                expected_salary = :expected_salary 
                            WHERE user_id = :user_id";

            $candidateStmt = $this->db->prepare($candidateSql);
            $candidateStmt->execute([
                'cv_path'         => $candidate->getCvPath(),
                'expected_salary' => $candidate->getExpectedSalary(),
                'user_id'         => $candidate->getUserId()
            ]);

            $this->deleteTags($candidate->getUserId());
            if (!empty($candidate->getTags())) {
                $this->saveTags($candidate->getUserId(), $candidate->getTags());
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getAll(): array
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, candidates.cv_path, candidates.expected_salary 
                FROM users 
                INNER JOIN candidates ON users.id = candidates.user_id";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $candidates = [];
        foreach ($rows as $row) {
            $role = $this->roleRepository->findById((int)$row['role_id']);
            $tags = $this->getCandidateTags((int)$row['id']);

            $candidates[] = new Candidate(
                (int)$row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['username'],
                $row['email'],
                $row['password'],
                $role,
                $row['cv_path'],
                $row['expected_salary'] ? (float)$row['expected_salary'] : null,
                $tags
            );
        }

        return $candidates;
    }

    private function saveTags(int $candidateId, array $tagIds): void
    {
        $sql = "INSERT INTO candidate_tags (candidate_id, tag_id) VALUES (:candidate_id, :tag_id)";
        $stmt = $this->db->prepare($sql);

        foreach ($tagIds as $tagId) {
            $stmt->execute([
                'candidate_id' => $candidateId,
                'tag_id' => is_object($tagId) ? $tagId->getId() : $tagId
            ]);
        }
    }

    private function deleteTags(int $candidateId): void
    {
        $sql = "DELETE FROM candidate_tags WHERE candidate_id = :candidate_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['candidate_id' => $candidateId]);
    }

    private function getCandidateTags(int $candidateId): array
    {
        $sql = "SELECT tags.* FROM tags 
                INNER JOIN candidate_tags ON tags.id = candidate_tags.tag_id 
                WHERE candidate_tags.candidate_id = :candidate_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['candidate_id' => $candidateId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return new Tag((int)$row['id'], $row['name']);
        }, $rows);
    }
}
