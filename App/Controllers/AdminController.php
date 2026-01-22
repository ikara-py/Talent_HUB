<?php

namespace App\Controllers;

class AdminController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->checkAuth('admin');
    }

    private function checkAuth($requiredRole)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('auth/login'));
            exit;
        }

        if ($_SESSION['user_role'] !== $requiredRole) {
            require_once '../app/views/errors/403.php';
            exit;
        }
    }

    public function index($name = 'Admin') 
    {
        $data = [
            'title' => 'Welcome to TalentHub Admin Dashboard',
            'user'  => $_SESSION['user_full_name'] ?? $name
        ];
        $this->view('admin/dashboard', $data);
    }

    public function dashboard()
    {
        $this->view('admin/dashboard');
    }
}