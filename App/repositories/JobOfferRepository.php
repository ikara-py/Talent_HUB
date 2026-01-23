<?php

namespace App\Repositories;

use App\Models\JobOffer;
use PDO;

class JobOfferRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    public function findByRecruiter(string $recruiterEmail): array
    {
        $sql = "SELECT o.* 
                FROM offers o
                INNER JOIN companies c ON o.company_id = c.user_id
                INNER JOIN users u ON c.user_id = u.id
                WHERE u.email = :email AND o.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $recruiterEmail]);

        $offers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $offers[] = new JobOffer(
                $row['id'],
                $row['title'],
                $row['description'],
                0, 
                false,
                (int)$row['category_id']
            );
        }

        return $offers;
    }


    public function create(JobOffer $job, string $recruiterEmail): bool
    {
        $sql = "SELECT user_id FROM users u
                INNER JOIN companies c ON u.id = c.user_id
                WHERE u.email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $recruiterEmail]);
        $companyId = $stmt->fetchColumn();

        $sql = "INSERT INTO offers (title, description, company_id, category_id)
                VALUES (:title, :description, :company_id, :category_id)";

        return $this->db->prepare($sql)->execute([
            'title' => $job->getTitle(),
            'description' => $job->getDescription(),
            'company_id' => $companyId,
            'category_id' => $job->getCategoryId()
        ]);
    }

    public function archive(int $jobId, int $recruiterId): bool
    {
        $sql = "UPDATE offers
                SET deleted_at = NOW()
                WHERE id = :id AND company_id = :rid";

        return $this->db->prepare($sql)->execute([
            'id' => $jobId,
            'rid' => $recruiterId
        ]);
    }
}
