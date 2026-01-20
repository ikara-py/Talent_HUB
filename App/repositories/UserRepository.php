<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\RoleRepository;
use Database;
use PDO;

class UserRepository
{
    private $db;
    private $roleRepository;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->roleRepository = new RoleRepository();
    }
    public function save(User $user): bool
    {
        $sql = "INSERT INTO users (first_name, last_name, email, password, role_id) 
                VALUES (:first_name, :last_name, :email, :password, :role_id)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'first_name' => $user->getFirstName(),
            'last_name'  => $user->getLastName(),
            'email'      => $user->getEmail(),
            'password'   => $user->getPassword(),
            'role_id'    => $user->getRole()->getId()
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }
        $role = $this->roleRepository->findById((int)$row['role_id']);
        return new User(
            $row['first_name'],
            $row['last_name'],
            $row['username'],
            $row['email'],
            $row['password'],
            $role
        );
    }
}