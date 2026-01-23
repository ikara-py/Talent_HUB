<?php

namespace App\Controllers;

use App\Repositories\JobOfferRepository;
use App\Repositories\ApplicationRepository;
use App\Models\JobOffer;
use PDO;

class RecruiterController extends Controller
{
    /**
     * Guard: ensure user is logged in and is a recruiter
     */
    private function requireRecruiter(): void
    {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            http_response_code(403);
            die('403 - Not authenticated');
        }

        if ($_SESSION['user_role'] !== 'recruiter') {
            http_response_code(403);
            die('403 - Access denied');
        }
    }

    /**
     * Helper: get numeric recruiter ID from session email
     */
    private function getRecruiterId(): int
    {
        $email = $_SESSION['user_id']; // email stored in session
        $db = new \PDO('mysql:host=localhost;dbname=th_v2;charset=utf8', 'root', ''); // Adjust DB credentials if needed
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user['id'] ?? 0;
    }

    /**
     * Recruiter Dashboard
     */
    public function dashboard(): void
    {
        $this->requireRecruiter();

        $repo = new JobOfferRepository();
        $recruiterId = $this->getRecruiterId(); // ✅ numeric ID
        $jobs = $repo->findByRecruiter($recruiterId);

        $this->view('recruiter/dashboard', [
            'jobs' => $jobs
        ]);
    }

    /**
     * Show form to create new job offer
     */
    public function createJob(): void
    {
        $this->requireRecruiter();

        $this->view('recruiter/job_create');
    }

    /**
     * Store new job offer
     */
    public function storeJob(): void
    {
        $this->requireRecruiter();

        $repo = new JobOfferRepository();
        $recruiterId = $this->getRecruiterId(); // ✅ numeric ID

        $repo->create(new JobOffer(
            null,
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            $recruiterId, // numeric company_id
            false,        // isArchived
            1             // default category
        ));

        header('Location: /recruiter/dashboard');
        exit;
    }

    /**
     * Archive a job offer
     */
    public function archiveJob(int $id): void
    {
        $this->requireRecruiter();

        $repo = new JobOfferRepository();
        $recruiterId = $this->getRecruiterId();
        $repo->archive($id, $recruiterId);

        header('Location: /recruiter/dashboard');
        exit;
    }

    /**
     * View applications for a job offer
     */
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
