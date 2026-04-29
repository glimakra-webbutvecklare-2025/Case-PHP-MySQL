<?php

// Databasuppgifter (anpassa efter din miljö)
//define('DB_HOST', getenv('DATABASE_HOST')); // Matchar service-namnet i docker-compose.yml
define('DB_HOST', getenv('DATABASE_HOST')); // Matchar service-namnet i docker-compose.yml
define('DB_NAME', getenv('MYSQL_DATABASE'));
define('DB_USER', getenv('MYSQL_USER'));
define('DB_PASS', getenv('MYSQL_PASSWORD'));

// teckenkodning
define('DB_CHARSET', 'utf8mb4');

// URL till sidan
define('BASE_URL', 'http://localhost:8050');

// Starta sessioner (viktigt för login!)
// Görs en gång här så det gäller alla sidor som inkluderar config.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Aktivera felrapportering under utveckling
// Stäng av på en produktionsserver!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);