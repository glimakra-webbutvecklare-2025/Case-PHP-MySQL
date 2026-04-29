<?php
require_once "config.php";

/**
 * Skapar och returnerar en PDO-databasanslutning.
 *
 * @return PDO PDO-anslutningsobjektet.
 * @throws PDOException Om anslutningen misslyckas.
 */
function connect_db() : PDO {
    $dsn = "mysql:host=mysql;port=3306;dbname=". DB_NAME .";user=". DB_USER .";password=" . DB_PASS . ";charset=" . DB_CHARSET;

    // Alternativ för PDO-anslutningen
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Kasta exceptions vid fel
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Hämta resultat som associativa arrayer
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Använd prepared statements
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        throw new PDOException("Kunde inte ansluta till databasen. Försök igen senare.", (int)$e->getCode());
    }
}