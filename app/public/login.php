<?php
declare(strict_types=1);
require_once 'includes/config.php';
require_once 'includes/database.php';

// Variabler som används senare
$errors = [];
$username = '';
$registration_success = isset($_GET['registered']) && $_GET['registered'] === 'success';

// Hantera en POST request
// dvs en användare skickara data via formuläret
// med HTTP POST metoden

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    /// hämta ut datan ur formuläret
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validering
    if (empty($username)) {
        $errors[] = 'Användarnamn är obligatoriskt.';
    }
    if (empty($password)) {
        $errors[] = 'Lösenord är obligatoriskt.';
    } 
   
    if (empty($errors)) {
        try {
            /// hämta pdo
            $pdo = connect_db();

            // hämta användare från databasen
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = :username");
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                // Spara informationen att du är inloggad
                    session_regenerate_id(true);  // Säkerhetsåtgärd mot session fixation
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];

                /// redirect till admin dashboard
                header('Location: admin/index.php');
                exit;
            } else {
                /// om något går fel, meddela användaren
                $errors[] = 'Felaktigt användarnamn eller lösenord.';
            }
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            $errors[] = 'Databasfel. Kan inte logga in användare just nu.';
        }
    }

    /// verifera att datan är korrekt t.ex bekräfta lösenord
}

?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logga in - Enkel Blogg</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <?php include "includes/nav.php" ?>

    <?php if ($registration_success): ?>
        <p class="success-message">Registreringen lyckades! Du kan nu logga in.</p>
    <?php endif; ?>    
    <h1>Logga in</h1>
    <?php if (!empty($errors)): ?>
        <?php $error_title = "Inloggningen misslyckades:"; ?>
        <?php include 'includes/view-errors.php' ?>
    <?php endif; ?>
    
    <form action="login.php" method="post">
        <div class="form-group">
            <label for="username">Användarnamn:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Lösenord:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Logga in</button>
    </form>

    <p>Har du inget konto? <a href="register.php">Registrera dig här</a>.</p>
</body>
</html>