<?php

namespace App\Repositories;

use App\Models\Application;
use Database;
use PDO;

class ApplicationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function findByJob(int $jobId): array
    {
        $sql = "SELECT * FROM applications WHERE job_offer_id = :jid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['jid' => $jobId]);

        $applications = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $applications[] = new Application(
                $row['id'],
                $row['candidate_name'],
                $row['cv_path'],
                $row['status']
            );
        }
        return $applications;
    }
}
