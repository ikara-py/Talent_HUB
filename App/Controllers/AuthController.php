<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\RegistrationService;
use App\Repositories\RoleRepository;

class AuthController extends Controller
{
    private AuthService $authService;
    private RegistrationService $registrationService;
    private RoleRepository $roleRepository;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
        $this->registrationService = new RegistrationService();
        $this->roleRepository = new RoleRepository();
    }

    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->authService->login($email, $password);

            if ($user) {
                $this->redirectToDashboard();
            } else {
                $this->view('auth/login', ['error' => 'Invalid email or password.']);
            }
        } else {
            $this->view('auth/login');
        }
    }

    public function register()
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard();
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $roleName = $_POST['role'] ?? 'candidate';

            $result = $this->registrationService->register(
                $firstName,
                $lastName,
                $username,
                $email,
                $password,
                $roleName
            );

            if ($result['success']) {
                header('Location: ' . url('auth/login'));
                exit;
            } else {
                $roles = $this->roleRepository->getAll();
                $this->view('auth/register', [
                    'error' => $result['message'],
                    'roles' => $roles
                ]);
            }
        } else {
            $roles = $this->roleRepository->getAll();
            $this->view('auth/register', ['roles' => $roles]);
        }
    }

    public function logout()
    {
        $this->authService->logout();
        header('Location: ' . url('auth/login'));
        exit;
    }

    private function redirectToDashboard()
    {
        $role = $_SESSION['user_role'] ?? '';

        $dashboards = [
            'admin'     => 'admin/dashboard',
            'recruiter' => 'recruiter/dashboard',
            'candidate' => 'candidate/dashboard',
        ];

        $route = $dashboards[$role] ?? '';

        header('Location: ' . url($route));
        exit;
    }
}