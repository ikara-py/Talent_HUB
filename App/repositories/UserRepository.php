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
        $sql = "INSERT INTO users (first_name, last_name, username, email, password, role_id) 
                VALUES (:first_name, :last_name, :username, :email, :password, :role_id)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'first_name' => $user->getFirstName(),
            'last_name'  => $user->getLastName(),
            'username'   => $user->getUsername(),
            'email'      => $user->getEmail(),
            'password'   => $user->getPassword(),
            'role_id'    => $user->getRole()->getId()
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, users.created_at 
                FROM users 
                WHERE users.email = :email";
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
            $role,
            (int)$row['id']
        );
    }

    public function findById(int $id): ?User
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, users.created_at 
                FROM users 
                WHERE users.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

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
            $role,
            (int)$row['id']
        );
    }

    public function getAll(): array
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, users.created_at, roles.name as role_name 
                FROM users 
                INNER JOIN roles ON users.role_id = roles.id 
                ORDER BY users.created_at DESC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $role = $this->roleRepository->findById((int)$row['role_id']);
            $users[] = new User(
                $row['first_name'],
                $row['last_name'],
                $row['username'],
                $row['email'],
                $row['password'],
                $role,
                (int)$row['id']
            );
        }

        return $users;
    }

    public function getAllByRole(string $roleName): array
    {
        $sql = "SELECT users.id, users.first_name, users.last_name, users.username, users.email, users.password, users.role_id, users.created_at 
                FROM users 
                INNER JOIN roles ON users.role_id = roles.id 
                WHERE roles.name = :role_name 
                ORDER BY users.first_name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['role_name' => $roleName]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];
        foreach ($rows as $row) {
            $role = $this->roleRepository->findById((int)$row['role_id']);
            $users[] = new User(
                $row['first_name'],
                $row['last_name'],
                $row['username'],
                $row['email'],
                $row['password'],
                $role,
                (int)$row['id']
            );
        }

        return $users;
    }

    public function update(User $user): bool
    {
        try {
            $sql = "UPDATE users 
                    SET first_name = :first_name, 
                        last_name = :last_name, 
                        username = :username, 
                        email = :email, 
                        role_id = :role_id 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'role_id' => $user->getRole()->getId(),
                'id' => $user->getId()
            ]);
        } catch (\PDOException $e) {
            error_log("Failed to update user: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            error_log("Failed to delete user: " . $e->getMessage());
            return false;
        }
    }

    public function getLastInsertId(): int
    {
        return (int)$this->db->lastInsertId();
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM users";
        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }
}
