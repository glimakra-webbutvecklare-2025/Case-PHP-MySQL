<?php
declare(strict_types=1);
require_once '../includes/config.php';

// skydda routen, det ska endast vara inloggande användare
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// hämta hem inloggad användares id
$logged_in_user_id = $_SESSION['user_id'];

// databaskontakt
require_once '../includes/database.php';
// post modell för att skapa ny post
require_once '../includes/Post.php';
$post_model = new Post(connect_db());

// variabler för formuläret
$errors = [];
$title = [];
$body = '';



// ta emot en data från formuläret
// dvs undersöka om requesten är POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']) ?? '';
    $body = trim($_POST['body']) ?? '';
    $image = $_FILES['uploaded-image'] ?? null;
    $image_path = null;

    // Validera så att title och body är ej tomma
    if (empty($title)) {
        $errors = ['Titel kan inte vara tom'];
    }
    if (empty($body)) {
        $errors = ['Innehåll kan inte vara tom'];
    }

    // bildhantering
    // kolla om det är en felfri uppladdning

    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 7 * 1024 * 1024; // 7 MB

        // dubbelkolla att filtype är tillåten
        if (!in_array($image['type'], $allowed_types)) {
            $errors[] = "Ogiltig filtyp. Endast jpeg, png eller gif.";
        } 
        
        if ($image['size'] > $max_size) {
            $errors[] = "Filen är för stor. Maxstorlek är 7 MB";
        }
        
        if (empty($errors)) {
            // förbereda inför att flytta bilden till uploads/ mappen
            
            $file_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
            $unique_filename = uniqid('post_img_', true) . "." . $file_extension;
            $destination = UPLOAD_PATH . $unique_filename;

            if (move_uploaded_file($image['tmp_name'], $destination)) {
                $image_path = 'uploads/' . $unique_filename;
            } else {
                $errors[] = 'Kunde inte ladda upp bilden. Kontrollera att mappen finns och har korrekt rättigheter';
            }
        }
    }
    if ($image && $image['error'] !== UPLOAD_ERR_OK) {
        print_r($image);
        $errors[] = 'Ett fel uppstod vid bilduppladdning';
    }

    // om både title och body är OK så skapa en ny post med
    if (empty($errors)) {
        try {
            // skapa en post
            $post_model->create($logged_in_user_id, $title, $body, $image_path);

            // redirect till admin/index.php och lägg till ?created=success
            header('Location: index.php?created=success');
        } catch (PDOException $e) {
            error_log("Create Post error", $e->getMessage());
            $errors[] = 'Databasfel. Kan inte spara inlägg just nu.';
            // om uppladdningen gick bra men databasen misslyckas 
            // kan vi ta bort filen
            if ($image_path && file_exists(UPLOAD_PATH . basename($image_path))) {
                unlink(UPLOAD_PATH . basename($image_path));
            }
        }

    }
    

    // fånga eventuella fel
}

?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skapa nytt inlägg - Admin</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
    <h1>Skapa nytt blogginlägg</h1>
    <?php if (!empty($errors)): ?>
        <?php $error_title = "Inlägget kunde inte sparas:"; ?>
        <?php include 'includes/view-errors.php' ?>
    <?php endif; ?>
    <p><a href="index.php">&laquo; Tillbaka till Admin Dashboard</a></p>

    <form action="create_post.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Titel:</label>
            <input type="text" id="title" name="title" required>
        </div>
        <div class="form-group">
            <label for="body">Innehåll:</label>
            <textarea id="body" name="body" required></textarea>
        </div>
        <div class="form-group">
            <label for="uploaded-image">Bild (valfritt, max 7 MB, JPG/PNG/GIF):</label>
            <input type="file" id="image" name="uploaded-image" accept="image/jpeg, image/png, image/gif">
        </div>
        <button type="submit">Spara inlägg</button>
    </form>
</body>
</html>