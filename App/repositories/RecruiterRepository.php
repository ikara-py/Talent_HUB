<?php

namespace App\Repositories;

use App\Models\Recruiter;
use App\Models\Role;
use Database;
use PDO;

class RecruiterRepository
{
    private $db;
    private $roleRepository;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->roleRepository = new RoleRepository();
    }

    public function save(Recruiter $recruiter): bool
    {
        try {
            $this->db->beginTransaction();

            $userSql = "INSERT INTO users (first_name, last_name, username, email, password, role_id) 
                        VALUES (:first_name, :last_name, :username, :email, :password, :role_id)";
            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute([
                'first_name' => $recruiter->getFirstName(),
                'last_name'  => $recruiter->getLastName(),
                'username'   => $recruiter->getUsername(),
                'email'      => $recruiter->getEmail(),
                'password'   => $recruiter->getPassword(),
                'role_id'    => $recruiter->getRole()->getId()
            ]);

            $userId = $this->db->lastInsertId();

            $recruiterSql = "INSERT INTO companies (user_id, company_name, company_description, website_url) 
                            VALUES (:user_id, :company_name, :company_description, :website_url)";
            $recruiterStmt = $this->db->prepare($recruiterSql);
            $recruiterStmt->execute([
                'user_id'             => $userId,
                'company_name'        => $recruiter->getCompanyName(),
                'company_description' => $recruiter->getDescription(),
                'website_url'         => $recruiter->getWebsite()
            ]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function findByUserId(int $userId): ?Recruiter
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, companies.company_name, companies.company_description, companies.website_url 
                FROM users 
                INNER JOIN companies ON users.id = companies.user_id 
                WHERE users.id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $role = $this->roleRepository->findById((int)$row['role_id']);

        return new Recruiter(
            (int)$row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['username'],
            $row['email'],
            $row['password'],
            $role,
            $row['company_name'],
            $row['company_description'],
            $row['website_url']
        );
    }

    public function findByEmail(string $email): ?Recruiter
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, companies.company_name, companies.company_description, companies.website_url 
                FROM users 
                INNER JOIN companies ON users.id = companies.user_id 
                WHERE users.email = :email";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $role = $this->roleRepository->findById((int)$row['role_id']);

        return new Recruiter(
            (int)$row['id'],
            $row['first_name'],
            $row['last_name'],
            $row['username'],
            $row['email'],
            $row['password'],
            $role,
            $row['company_name'],
            $row['description'],
            $row['website']
        );
    }

    public function update(Recruiter $recruiter): bool
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
                'first_name' => $recruiter->getFirstName(),
                'last_name'  => $recruiter->getLastName(),
                'username'   => $recruiter->getUsername(),
                'email'      => $recruiter->getEmail(),
                'user_id'    => $recruiter->getUserId()
            ]);

            $recruiterSql = "UPDATE companies 
                            SET company_name = :company_name, 
                                company_description = :company_description, 
                                website_url = :website_url 
                            WHERE user_id = :user_id";

            $recruiterStmt = $this->db->prepare($recruiterSql);
            $recruiterStmt->execute([
                'company_name'        => $recruiter->getCompanyName(),
                'company_description' => $recruiter->getDescription(),
                'website_url'         => $recruiter->getWebsite(),
                'user_id'             => $recruiter->getUserId()
            ]);

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function getAll(): array
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, companies.company_name, companies.company_description, companies.website_url 
                FROM users 
                INNER JOIN companies ON users.id = companies.user_id";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $recruiters = [];
        foreach ($rows as $row) {
            $role = $this->roleRepository->findById((int)$row['role_id']);

            $recruiters[] = new Recruiter(
                (int)$row['id'],
                $row['first_name'],
                $row['last_name'],
                $row['username'],
                $row['email'],
                $row['password'],
                $role,
                $row['company_name'],
                $row['description'],
                $row['website']
            );
        }

        return $recruiters;
    }

    public function hasCompanyProfile(int $userId): bool
    {
        $sql = "SELECT COUNT(*) FROM companies WHERE user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
