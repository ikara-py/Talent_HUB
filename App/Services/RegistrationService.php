<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;

class RegistrationService
{
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->roleRepository = new RoleRepository();
    }

    public function register(string $firstName, string $lastName, string $username, string $email, string $password, string $roleName
    ): array {
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'Email already registered.'
            ];
        }

        $role = $this->roleRepository->findByName($roleName);
        if (!$role) {
            return [
                'success' => false,
                'message' => 'Invalid role selected.'
            ];
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $user = new User(
            $firstName,
            $lastName,
            $username,
            $email,
            $hashedPassword,
            $role
        );

        $saved = $this->userRepository->save($user);
        if ($saved) {
            return [
                'success' => true,
                'message' => 'Registration successful!'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }
}