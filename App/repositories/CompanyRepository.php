<?php

namespace App\Repositories;

use App\Models\Company;
use Database;
use PDO;

class CompanyRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getAll(): array
    {
        $sql = "SELECT companies.user_id, companies.company_name, companies.company_description, companies.website_url, users.first_name, users.last_name, users.email, users.username 
                FROM companies 
                INNER JOIN users ON companies.user_id = users.id 
                ORDER BY companies.company_name ASC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'mapRowToCompany'], $rows);
    }

    public function findById(int $userId): ?Company
    {
        $sql = "SELECT companies.user_id, companies.company_name, companies.company_description, companies.website_url, users.first_name, users.last_name, users.email, users.username 
                FROM companies 
                INNER JOIN users ON companies.user_id = users.id 
                WHERE companies.user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToCompany($row);
    }

    public function save(Company $company): bool
    {
        try {
            $sql = "INSERT INTO companies (user_id, company_name, company_description, website_url) 
                    VALUES (:user_id, :company_name, :company_description, :website_url)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'user_id' => $company->getUserId(),
                'company_name' => $company->getCompanyName(),
                'company_description' => $company->getCompanyDescription(),
                'website_url' => $company->getWebsiteUrl()
            ]);
        } catch (\PDOException $e) {
            error_log("Failed to save company: " . $e->getMessage());
            return false;
        }
    }

    public function update(Company $company): bool
    {
        try {
            $sql = "UPDATE companies 
                    SET company_name = :company_name, 
                        company_description = :company_description, 
                        website_url = :website_url 
                    WHERE user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'company_name' => $company->getCompanyName(),
                'company_description' => $company->getCompanyDescription(),
                'website_url' => $company->getWebsiteUrl(),
                'user_id' => $company->getUserId()
            ]);
        } catch (\PDOException $e) {
            error_log("Failed to update company: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $userId): bool
    {
        try {
            $sql = "DELETE FROM companies WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['user_id' => $userId]);
        } catch (\PDOException $e) {
            error_log("Failed to delete company: " . $e->getMessage());
            return false;
        }
    }

    public function getRecruitersWithoutCompany(): array
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.email, users.username 
                FROM users 
                INNER JOIN roles ON users.role_id = roles.id 
                LEFT JOIN companies ON users.id = companies.user_id 
                WHERE roles.name = 'recruiter' AND companies.user_id IS NULL 
                ORDER BY users.first_name ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM companies";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    private function mapRowToCompany(array $row): Company
    {
        return new Company(
            (int)$row['user_id'],
            $row['company_name'],
            $row['company_description'],
            $row['website_url'],
            $row['first_name'] ?? null,
            $row['last_name'] ?? null,
            $row['email'] ?? null,
            $row['username'] ?? null
        );
    }
}
