<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function login(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);


        if ($user && password_verify($password, $user->getPassword())) {
            $this->createSession($user);
            return $user;
        }

        return null;
    }

    private function createSession(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->getId();
        $_SESSION['user_email'] = $user->getEmail();
        $_SESSION['user_full_name'] = "{$user->getFirstName()} {$user->getLastName()}";
        $_SESSION['user_role'] = $user->getRole()->getName();
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_unset();
        session_destroy();
    }
}
