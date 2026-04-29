<?php
declare(strict_types=1);
require_once 'includes/config.php';

$_SESSION = [];  // Töm sessionen först

// ta bort cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy(); 

print_r($_SESSION);

// redirect
header('Location: index.php?logout=success');
?>
