<?php

namespace App\Repositories;

use App\Models\Category;
use Database;
use PDO;

class CategoryRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function save(Category $category): bool
    {
        $sql = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute(['name' => $category->getName()]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function findById(int $id): ?Category
    {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Category((int)$row['id'], $row['name']);
    }

    public function findByName(string $name): ?Category
    {
        $sql = "SELECT * FROM categories WHERE name = :name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['name' => $name]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Category((int)$row['id'], $row['name']);
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categories = [];
        foreach ($rows as $row) {
            $categories[] = new Category((int)$row['id'], $row['name']);
        }

        return $categories;
    }

    public function update(Category $category): bool
    {
        $sql = "UPDATE categories SET name = :name WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([
                'name' => $category->getName(),
                'id' => $category->getId()
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as total FROM categories";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }
}
