<?php

namespace App\Repositories;

use App\Models\JobOffer;
use PDO;

require_once __DIR__ . '/../../config/connection.php';

class JobOfferRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = \Database::connect();
    }

    public function findByRecruiter(int $recruiterId): array
    {
        // Use the 'offers' table and link to recruiter via 'companies'
        $sql = "SELECT o.* 
                FROM offers o
                INNER JOIN companies c ON o.company_id = c.user_id
                WHERE c.user_id = :rid AND o.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['rid' => $recruiterId]);

        $offers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $offers[] = new JobOffer(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['company_id'], // recruiter ID
                false // no is_archived column in DB
            );
        }
        return $offers;
    }

    public function create(JobOffer $job): bool
    {
        $sql = "INSERT INTO offers (title, description, company_id, category_id)
                VALUES (:title, :description, :company_id, :category_id)";

        return $this->db->prepare($sql)->execute([
            'title' => $job->getTitle(),
            'description' => $job->getDescription(),
            'company_id' => $job->getRecruiterId(),
            'category_id' => $job->getCategoryId() ?? 1
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
