<?php
declare(strict_types=1);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';



$pdo = connect_db();

print_r($pdo);

// Tabellen users

// sql fråga för att skapa tabellen users
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL, -- Lagrar hashat lösenord
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";


$stmt = $pdo->query($sql);

if (!$stmt) {
    echo "Tabellen users kunde inte skapas";
}

// Tabellen posts

$sql = "CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,             -- Vem som skrev inlägget
    title VARCHAR(255) NOT NULL,      -- Inläggets titel
    body TEXT NOT NULL,               -- Innehållet i inlägget
    image_path VARCHAR(255) NULL,     -- Sökväg till uppladdad bild (valfritt)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- När inlägget senast ändrades
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE -- Koppling till users. Om användaren raderas, raderas även hens inlägg.
);";


$stmt = $pdo->query($sql);

if (!$stmt) {
    echo "Tabellen posts kunde inte skapas";
}


// instruktion för att visa tabeller i databasen

$sql = "SHOW tables";
$stmt = $pdo->query($sql);

// visa resultat

$result = $stmt->fetchAll();

print_r($result);