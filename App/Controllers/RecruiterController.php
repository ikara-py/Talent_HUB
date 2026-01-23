<?php

namespace App\Controllers;

use App\Repositories\JobOfferRepository;
use App\Repositories\ApplicationRepository;

class RecruiterController extends Controller
{
    private function requireRecruiter(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'], $_SESSION['user_role'])) {
            http_response_code(403);
            die('403 - Not authenticated');
        }

        if ($_SESSION['user_role'] !== 'recruiter') {
            http_response_code(403);
            die('403 - Access denied');
        }

        return $_SESSION['user_id'];
    }



    public function dashboard(): void
    {
        $recruiterId = $this->requireRecruiter();

        $repo = new JobOfferRepository();
        $jobs = $repo->findByRecruiter($recruiterId);

        $this->view('recruiter/dashboard', [
            'jobs' => $jobs
        ]);
    }

    public function createJob(): void
    {
        $this->requireRecruiter();
        $this->view('recruiter/job_create');
    }

    public function storeJob(): void
    {
        $recruiterId = $this->requireRecruiter();

        $repo = new JobOfferRepository();

        $job = new \App\Models\JobOffer(
            null,
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            $recruiterId,
            false,
            (int)($_POST['category_id'] ?? 1)
        );

        $repo->create($job);

        header('Location: /recruiter/dashboard');
        exit;
    }

    public function archiveJob(int $id): void
    {
        $recruiterId = $this->requireRecruiter();

        $repo = new JobOfferRepository();
        $repo->archive($id, $recruiterId);

        header('Location: /recruiter/dashboard');
        exit;
    }

    public function applications(int $jobId): void
    {
        $this->requireRecruiter();

        $repo = new ApplicationRepository();
        $applications = $repo->findByJob($jobId);

        $this->view('recruiter/applications', [
            'applications' => $applications
        ]);
    }
}
