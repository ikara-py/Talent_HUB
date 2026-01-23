<?php

namespace App\Repositories;

use App\Models\Recruiter;
use App\Repositories\RoleRepository;
use PDO;

// IMPORTANT: manually load Database class
require_once __DIR__ . '/../../config/connection.php';

class RecruiterRepository
{
    private PDO $db;
    private RoleRepository $roleRepository;

    public function __construct()
    {
        // Database is in the global namespace
        $this->db = \Database::connect();
        $this->roleRepository = new RoleRepository();
    }

    public function save(Recruiter $recruiter): bool
    {
        $sql = "INSERT INTO users 
                (first_name, last_name, username, email, password, role_id, company_id)
                VALUES 
                (:first_name, :last_name, :username, :email, :password, :role_id, :company_id)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'first_name' => $recruiter->getFirstName(),
            'last_name'  => $recruiter->getLastName(),
            'username'   => $recruiter->getUsername(),
            'email'      => $recruiter->getEmail(),
            'password'   => $recruiter->getPassword(),
            'role_id'    => $recruiter->getRole()->getId(),
            'company_id' => $recruiter->getCompanyId()
        ]);
    }

    public function findByEmail(string $email): ?Recruiter
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $role = $this->roleRepository->findById((int)$row['role_id']);

        return new Recruiter(
            $row['first_name'],
            $row['last_name'],
            $row['username'],
            $row['email'],
            $row['password'],
            $role,
            $row['company_id'] ?? null
        );
    }

    public function assignCompany(int $userId, int $companyId): bool
    {
        $sql = "UPDATE users SET company_id = :company_id WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'company_id' => $companyId,
            'id' => $userId
        ]);
    }
}
