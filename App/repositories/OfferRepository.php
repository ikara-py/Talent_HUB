<?php

namespace App\Repositories;

use App\Models\Offer;
use App\Models\Category;
use App\Models\Tag;
use Database;
use PDO;

class OfferRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function save(Offer $offer): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO offers (company_id, category_id, title, description, location, salary) 
                    VALUES (:company_id, :category_id, :title, :description, :location, :salary)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'company_id' => $offer->getCompanyId(),
                'category_id' => $offer->getCategoryId(),
                'title' => $offer->getTitle(),
                'description' => $offer->getDescription(),
                'location' => $offer->getLocation(),
                'salary' => $offer->getSalary()
            ]);

            $offerId = $this->db->lastInsertId();

            if (!empty($offer->getTags())) {
                $this->saveTags($offerId, $offer->getTags());
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Failed to save offer: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return false;
        }
    }

    public function update(Offer $offer): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE offers 
                    SET category_id = :category_id, 
                        title = :title, 
                        description = :description, 
                        location = :location, 
                        salary = :salary 
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'category_id' => $offer->getCategoryId(),
                'title' => $offer->getTitle(),
                'description' => $offer->getDescription(),
                'location' => $offer->getLocation(),
                'salary' => $offer->getSalary(),
                'id' => $offer->getId()
            ]);

            $this->deleteTags($offer->getId());
            if (!empty($offer->getTags())) {
                $this->saveTags($offer->getId(), $offer->getTags());
            }

            $this->db->commit();
            return true;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Failed to update offer: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return false;
        }
    }

    public function softDelete(int $id): bool
    {
        $sql = "UPDATE offers SET deleted_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function restore(int $id): bool
    {
        $sql = "UPDATE offers SET deleted_at = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function findById(int $id): ?Offer
    {
        $sql = "SELECT offers.id, offers.company_id, offers.category_id, offers.title, offers.description, offers.location, offers.salary, offers.created_at, offers.deleted_at, categories.name as category_name, companies.company_name 
                FROM offers 
                LEFT JOIN categories ON offers.category_id = categories.id 
                LEFT JOIN companies ON offers.company_id = companies.user_id 
                WHERE offers.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->mapRowToOffer($row);
    }

    public function getAll(bool $includeDeleted = false): array
    {
        $sql = "SELECT offers.id, offers.company_id, offers.category_id, offers.title, offers.description, offers.location, offers.salary, offers.created_at, offers.deleted_at, categories.name as category_name, companies.company_name 
                FROM offers 
                LEFT JOIN categories ON offers.category_id = categories.id 
                LEFT JOIN companies ON offers.company_id = companies.user_id";

        if (!$includeDeleted) {
            $sql .= " WHERE offers.deleted_at IS NULL";
        }

        $sql .= " ORDER BY offers.created_at DESC";

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'mapRowToOffer'], $rows);
    }

    public function getByCompanyId(int $companyId, bool $includeDeleted = false): array
    {
        $sql = "SELECT offers.id, offers.company_id, offers.category_id, offers.title, offers.description, offers.location, offers.salary, offers.created_at, offers.deleted_at, categories.name as category_name, companies.company_name 
                FROM offers 
                LEFT JOIN categories ON offers.category_id = categories.id 
                LEFT JOIN companies ON offers.company_id = companies.user_id 
                WHERE offers.company_id = :company_id";

        if (!$includeDeleted) {
            $sql .= " AND offers.deleted_at IS NULL";
        }

        $sql .= " ORDER BY offers.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['company_id' => $companyId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'mapRowToOffer'], $rows);
    }

    public function searchOffers(?string $keyword = null, ?int $categoryId = null, ?array $tagIds = null): array
    {
        $sql = "SELECT DISTINCT offers.id, offers.company_id, offers.category_id, offers.title, offers.description, offers.location, offers.salary, offers.created_at, offers.deleted_at, categories.name as category_name, companies.company_name 
                FROM offers 
                LEFT JOIN categories ON offers.category_id = categories.id 
                LEFT JOIN companies ON offers.company_id = companies.user_id 
                LEFT JOIN offer_tags ON offers.id = offer_tags.offer_id 
                WHERE offers.deleted_at IS NULL";

        $params = [];

        if ($keyword) {
            $sql .= " AND (offers.title LIKE :keyword OR offers.description LIKE :keyword OR offers.location LIKE :keyword)";
            $params['keyword'] = "%{$keyword}%";
        }

        if ($categoryId) {
            $sql .= " AND offers.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        if ($tagIds && !empty($tagIds)) {
            $placeholders = implode(',', array_fill(0, count($tagIds), '?'));
            $sql .= " AND offer_tags.tag_id IN ({$placeholders})";
            $params = array_merge($params, $tagIds);
        }

        $sql .= " ORDER BY offers.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([$this, 'mapRowToOffer'], $rows);
    }

    public function count(bool $includeDeleted = false): int
    {
        $sql = "SELECT COUNT(*) FROM offers";
        if (!$includeDeleted) {
            $sql .= " WHERE deleted_at IS NULL";
        }

        $stmt = $this->db->query($sql);
        return (int)$stmt->fetchColumn();
    }

    private function saveTags(int $offerId, array $tagIds): void
    {
        $sql = "INSERT INTO offer_tags (offer_id, tag_id) VALUES (:offer_id, :tag_id)";
        $stmt = $this->db->prepare($sql);

        foreach ($tagIds as $tagId) {
            $stmt->execute([
                'offer_id' => $offerId,
                'tag_id' => $tagId
            ]);
        }
    }

    private function deleteTags(int $offerId): void
    {
        $sql = "DELETE FROM offer_tags WHERE offer_id = :offer_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId]);
    }

    private function getOfferTags(int $offerId): array
    {
        $sql = "SELECT tags.* FROM tags 
                INNER JOIN offer_tags ON tags.id = offer_tags.tag_id 
                WHERE offer_tags.offer_id = :offer_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['offer_id' => $offerId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return new Tag((int)$row['id'], $row['name']);
        }, $rows);
    }

    private function mapRowToOffer(array $row): Offer
    {
        $tags = $this->getOfferTags((int)$row['id']);
        $category = isset($row['category_name'])
            ? new Category((int)$row['category_id'], $row['category_name'])
            : null;

        return new Offer(
            (int)$row['id'],
            (int)$row['company_id'],
            (int)$row['category_id'],
            $row['title'],
            $row['description'],
            $row['location'],
            $row['salary'] ? (float)$row['salary'] : null,
            $row['created_at'],
            $row['deleted_at'],
            $tags,
            $category,
            $row['company_name'] ?? null
        );
    }
}
