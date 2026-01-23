<?php

namespace App\Services;

use App\Models\User;
use App\Models\Recruiter;
use App\Models\Candidate;
use App\Repositories\UserRepository;
use App\Repositories\RoleRepository;
use App\Repositories\RecruiterRepository;
use App\Repositories\CandidateRepository;

class RegistrationService
{
    private UserRepository $userRepository;
    private RoleRepository $roleRepository;
    private RecruiterRepository $recruiterRepository;
    private CandidateRepository $candidateRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->roleRepository = new RoleRepository();
        $this->recruiterRepository = new RecruiterRepository();
        $this->candidateRepository = new CandidateRepository();
    }

    public function register(
        string $firstName,
        string $lastName,
        string $username,
        string $email,
        string $password,
        string $roleName
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

        if ($roleName === 'recruiter') {
            return $this->registerRecruiter($firstName, $lastName, $username, $email, $hashedPassword, $role);
        } elseif ($roleName === 'candidate') {
            return $this->registerCandidate($firstName, $lastName, $username, $email, $hashedPassword, $role);
        } else {
            // Admin or other roles - just create user
            $user = new User($firstName, $lastName, $username, $email, $hashedPassword, $role);
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

    private function registerRecruiter(string $firstName, string $lastName, string $username, string $email, string $hashedPassword, $role): array
    {
        $recruiter = new Recruiter(
            0,
            $firstName,
            $lastName,
            $username,
            $email,
            $hashedPassword,
            $role,
            $firstName . "'s Company", 
            "Company description not yet provided.", 
            null 
        );

        $saved = $this->recruiterRepository->save($recruiter);

        if ($saved) {
            return [
                'success' => true,
                'message' => 'Registration successful! Please update your company profile.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }

    private function registerCandidate(string $firstName, string $lastName, string $username, string $email, string $hashedPassword, $role): array
    {
        $candidate = new Candidate(
            0, 
            $firstName,
            $lastName,
            $username,
            $email,
            $hashedPassword,
            $role,
            null, 
            null, 
            [] 
        );

        $saved = $this->candidateRepository->save($candidate);

        if ($saved) {
            return [
                'success' => true,
                'message' => 'Registration successful! Please complete your profile.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ];
        }
    }
}
