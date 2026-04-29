<?php
declare(strict_types=1);

class Post
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(int $userId, string $title, string $body, ?string $imagePath = null): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO posts (user_id, title, body, image_path)
             VALUES (:user_id, :title, :body, :image_path)"
        );

        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':body', $body);
        $stmt->bindValue(':image_path', $imagePath, $imagePath === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->pdo->lastInsertId();
    }

    public function showOne(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            "SELECT posts.*, users.username
             FROM posts
             JOIN users ON posts.user_id = users.id
             WHERE posts.id = :id"
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function showAll(): array
    {
        $stmt = $this->pdo->query(
            "SELECT posts.*, users.username
             FROM posts
             JOIN users ON posts.user_id = users.id
             ORDER BY posts.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function showAllByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, title, created_at, updated_at
             FROM posts
             WHERE user_id = :user_id
             ORDER BY created_at DESC"
        );
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function updateOne(int $id, int $userId, string $title, string $body, ?string $imagePath): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE posts
             SET title = :title, body = :body, image_path = :image_path
             WHERE id = :id AND user_id = :user_id"
        );

        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':body', $body);
        $stmt->bindValue(':image_path', $imagePath, $imagePath === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function deleteOne(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
