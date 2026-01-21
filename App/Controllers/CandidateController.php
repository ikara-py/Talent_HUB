<?php

namespace App\Controllers;

use App\Repositories\CandidateRepository;

class CandidateController extends Controller
{
    private CandidateRepository $candidateRepository;

    public function __construct()
    {
        parent::__construct();

        // Authorization check
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'candidate') {
            $this->view('errors/403');
            exit();
        }

        $this->candidateRepository = new CandidateRepository();
    }

    /**
     * Candidate dashboard
     */
    public function index(): void
    {
        $candidateId = $_SESSION['user_id'] ?? null;

        if (!$candidateId) {
            $this->view('errors/403');
            return;
        }

        $candidate = $this->candidateRepository->findById((int) $candidateId);

        if (!$candidate) {
            $this->view('errors/404');
            return;
        }

        $this->view('candidate/dashboard', [
            'title'     => 'Candidate Dashboard',
            'candidate' => $candidate
        ]);
    }

    /**
     * Candidate profile page
     */
    public function profile(): void
    {
        $candidateId = $_SESSION['user_id'] ?? null;

        if (!$candidateId) {
            $this->view('errors/403');
            return;
        }

        $candidate = $this->candidateRepository->findById((int) $candidateId);

        if (!$candidate) {
            $this->view('errors/404');
            return;
        }

        $this->view('candidate/profile', [
            'title'     => 'My Profile',
            'candidate' => $candidate
        ]);
    }
}
