<?php

namespace App\Controllers;

use App\Repositories\JobOfferRepository;
use App\Repositories\ApplicationRepository;

class RecruiterController extends Controller
{
    /**
     * Local guard (recruiter-only)
     */
    private function requireRecruiter(): void
    {
        if (!isset($_SESSION['user'])) {
            http_response_code(403);
            die('403 - Not authenticated');
        }

        if ($_SESSION['user']['role'] !== 'recruiter') {
            http_response_code(403);
            die('403 - Access denied');
        }
    }

    public function dashboard(): void
    {
        $this->requireRecruiter();

        $repo = new JobOfferRepository();
        $jobs = $repo->findByRecruiter($_SESSION['user']['id']);

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
        $this->requireRecruiter();

        $repo = new JobOfferRepository();
        $repo->create(new \App\Models\JobOffer(
            null,
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            $_SESSION['user']['id']
        ));

        header('Location: /recruiter/dashboard');
        exit;
    }

    public function archiveJob(int $id): void
    {
        $this->requireRecruiter();

        $repo = new JobOfferRepository();
        $repo->archive($id, $_SESSION['user']['id']);

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
