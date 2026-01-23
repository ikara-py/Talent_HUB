<?php

namespace App\Repositories;

use App\Models\Role;
use Database;
use PDO;

class RoleRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }
    public function findByName(string $name): ?Role
    {
        $stmt = $this->db->prepare("SELECT roles.id, roles.name FROM roles WHERE roles.name = :name");
        $stmt->execute(['name' => strtolower($name)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Role($row['id'], $row['name']);
    }
    public function findById(int $id): ?Role
    {
        $stmt = $this->db->prepare("SELECT roles.id, roles.name FROM roles WHERE roles.id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Role($row['id'], $row['name']);
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT roles.id, roles.name FROM roles");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $roles = [];
        foreach ($rows as $row) {
            $roles[] = new Role($row['id'], $row['name']);
        }

        return $roles;
    }
}
