<?php
declare(strict_types=1);

class Post {
    public function __construct(private PDO $pdo) {
    }

    public function create(string $title, string $body, ?string $image_path = "") {
        // Lägg in databaskod för att skapa en ny rad i tabellen
        // 0. validera att det inte finns redan samma titel
        
        // 1. skapa ett sql statement
        $stmt = $this->pdo->prepare("INSERT INTO posts (title, body, image_path) VALUES (:title, :body, :image_path)");
        
        // 2. lägga till argumenten till statment
        $stmt->bindValue(":title", $title); // bind titel
        $stmt->bindValue(":body", $body); // bind body
        $stmt->bindValue(":image_path", $image_path); // bind img_url
        
        // 3. köra statment
        $stmt->execute();

        // 4. returnera ett resultat
        return $this->pdo->lastInsertId(); 
    }

    public function showOne(int $id): array|false {
        // 1. skapa ett sql statement
        $stmt = $this->pdo->prepare("SELECT * FROM posts WHERE id = :id");
        // 2. bind argument
        $stmt->bindValue(":id", $id);
        // 3. köra statment
        $stmt->execute();
        // 4. returnerna resultatet
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function showAll(): array {
        // 1. skapa ett sql statement
        $stmt = $this->pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC");
        // 2. köra statment
        $stmt->execute();
        // 3. returnerna resultatet
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateOne(int $id, ?int $userId, string $title, string $body, ?string $image_path) {
        // 1. skapa ett sql statement
        $stmt = $this->pdo->prepare(
            "UPDATE posts 
            SET title = :title, body = :body, image_path = :image_path
            WHERE id = :id");
        // 2. bind argument
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":title", $title, PDO::PARAM_STR);
        $stmt->bindValue(":body", $body, PDO::PARAM_STR);
        $stmt->bindValue(":image_path", $image_path, PDO::PARAM_STR);
        // 3. köra statment
        $stmt->execute();

        // 4. returnerna resultatet
        // dvs om minst en rad har ändrats
        return $stmt->rowCount() > 0;
    }

    public function deleteOne(int $id) : bool {
        // 1. skapa ett sql statement
        $stmt = $this->pdo->prepare("DELETE FROM posts WHERE id = :id");
        // 2. bind argument
        $stmt->bindValue(":id", $id);
        // 3. köra statment
        $stmt->execute();

        // 4. returnerna resultatet
        // Om mer än en rad blir påverkad -> true
        // Om 0 rad blir påverkad -> false
        return $stmt->rowCount() > 0;
    }
}