<?php
$password = "mitt_lösenord";
echo "Hash 1: " . password_hash($password, PASSWORD_DEFAULT) . "\n";
echo "Hash 2: " . password_hash($password, PASSWORD_DEFAULT) . "\n";  // Kör igen – olika resultat!
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Verifierar: " . (password_verify('mitt_lösenord', $hash) ? "OK" : "Fel") . "\n";


?>

