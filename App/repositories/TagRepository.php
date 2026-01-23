<?php

namespace App\Repositories;

use App\Models\Tag;
use Database;
use PDO;

class TagRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function save(Tag $tag): bool
    {
        $sql = "INSERT INTO tags (name) VALUES (:name)";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute(['name' => $tag->getName()]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function findById(int $id): ?Tag
    {
        $sql = "SELECT * FROM tags WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Tag((int)$row['id'], $row['name']);
    }

    public function findByName(string $name): ?Tag
    {
        $sql = "SELECT * FROM tags WHERE name = :name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['name' => $name]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Tag((int)$row['id'], $row['name']);
    }

    public function getAll(): array
    {
        $sql = "SELECT * FROM tags ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $tags = [];
        foreach ($rows as $row) {
            $tags[] = new Tag((int)$row['id'], $row['name']);
        }

        return $tags;
    }

    public function update(Tag $tag): bool
    {
        $sql = "UPDATE tags SET name = :name WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([
                'name' => $tag->getName(),
                'id' => $tag->getId()
            ]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM tags WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute(['id' => $id]);
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function count(): int
    {
        $sql = "SELECT COUNT(*) as total FROM tags";
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }
}
