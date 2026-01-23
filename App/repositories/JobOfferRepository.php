<?php

namespace App\Repositories;

use App\Models\JobOffer;
use Database;
use PDO;

class JobOfferRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findByRecruiter(int $recruiterId): array
    {
        $sql = "SELECT * FROM job_offers WHERE recruiter_id = :rid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['rid' => $recruiterId]);

        $offers = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $offers[] = new JobOffer(
                $row['id'],
                $row['title'],
                $row['description'],
                $row['recruiter_id'],
                (bool)$row['is_archived']
            );
        }
        return $offers;
    }

    public function create(JobOffer $job): bool
    {
        $sql = "INSERT INTO job_offers (title, description, recruiter_id)
                VALUES (:title, :description, :rid)";

        return $this->db->prepare($sql)->execute([
            'title' => $job->getTitle(),
            'description' => $job->getDescription(),
            'rid' => $job->getRecruiterId()
        ]);
    }

    public function archive(int $jobId, int $recruiterId): bool
    {
        $sql = "UPDATE job_offers  
                SET is_archived = 1 
                WHERE id = :id AND recruiter_id = :rid";

        return $this->db->prepare($sql)->execute([
            'id' => $jobId,
            'rid' => $recruiterId
        ]);
    }
}
