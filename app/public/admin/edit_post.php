<?php
declare(strict_types=1);

// Hämta config databasuppgifter
require_once '../includes/config.php';

// Säkerställa att användaren är inloggad
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php?redirect='. urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$logged_in_user_id = $_SESSION['user_id'];

require_once '../includes/database.php';
require_once '../includes/Post.php';

// array för att spara eventuella fel
$errors = [];
//         $_GET['id']; // fungerar också men du har ingen validering
$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // vilken post ska uppdateras?
$post = null;

// Variabler som vi ska plocka ut från post efter att den har hämtats från DB
$title = '';
$body = '';
$current_image_path = null;

if ($post_id === false || $post_id <= 0) {
    $errors[] = "Ogiltigt post-id";
} else {
    try {

        // Försök hämta post från DB
        // post model
        $post_model = new Post(connect_db());
        // hämta posten med rätt id
        $post = $post_model->showOne($post_id);

        if (!$post) {
            $errors[] = "Posten hittade inte";
        } elseif ($post['user_id'] != $logged_in_user_id) {
            $errors[] = "Du har inte rätt att redigera posten.";
            $post = null;
        } else {
            $title = $post['title'];
            $body = $post['body'];
            $current_image_path = $post['image_path'];
        }
    } catch (PDOExepction $e) {
        error_log("Edit Post Fetch error", $e->getMessage());
        $errors[] = 'Databasfel. Kan inte hämta post för redigering.';
        $post = null;
    }
}

// Hantera en förfrågan från formuläret
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_title = trim($_POST['title'] ?? '');
    $new_body = trim($_POST['body'] ?? '');
    $new_image_path = $current_image_path;

    // hantera delete av gammal bild
    $delete_image = isset($_POST['delete_image']);

    // om vi vill ta bort bild
    // ta bort den.
    if ($delete_image && $current_image_path) {
        // verkligen kolla om bilden finns
        if (file_exists($current_image_path)) {
            // TODO: undersök hur man tar bort från disk
            unlink($current_image_path); // ta bort
        }

        // sätt paths till null
        $current_image_path = null;
        $new_image_path = null;
    }

    // hantar uppladdning av ny bild
    // kontrollera om image finns
    $image = $_FILES['image'];
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        // dubbelkolla om ok typer av fil
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowed_types)) {
            $errors[] = 'Ogiltig filtyp. Endast JPG, PNG och GIF.';
        }
        // kolla storlek
        $max_size = 7 * 1024 * 1024;
        if ($image['size'] > $max_size) {
            $errors[] = 'Filen är för stor. Max 7 MB.';
        }

        // om OK, flytta fil från tmp till uploads
        if (empty($errors)) {
            $file_extension = pathinfo($image['name'], PATHINFO_EXTENSION); // hämta extension (.jpg, .png, .gif)
            $unique_filename = uniqid('post_img_', true) . $file_extension; // generera unikt filnamn
            $destination = UPLOAD_PATH . $unique_filename; // hela sökvägen
            
            if (move_uploaded_file($image['tmp_name'], $destination)) {
                // säkerställer att gamla bilden verkligen, verkligen är borta
                if ($current_image_path && file_exists(UPLOAD_PATH . basename($current_image_path))) {
                    unlink(UPLOAD_PATH . basename($current_image_path));
                }
                // relativ sökväg som sparas i DB
                $new_image_path = 'uploads/' . $unique_filename;
                $image_uploaded = true;
            } else {
                $errors[] = 'Kunde inte ladda upp den nya bilden.';
            }
        }

       
    }



    // validera att datan är rimlig
    if (empty($new_title)) {
        $errors[] = 'Titel är obligatoriskt.';
    }
    if (empty($new_body)) {
        $errors[] = 'Innehåll är obligatoriskt.';
    }

    if (empty($errors)) {
        try {
            // Försök uppdatera posten
            $post_model = new Post(connect_db());
            $updated_successfully = $post_model->updateOne($post_id, 
                                                            $logged_in_user_id, 
                                                            $new_title, 
                                                            $new_body, 
                                                            $new_image_path);
            
            if ($updated_successfully) {
                header("Location: index.php?updated=success&id=" . $post_id);
                exit;
            } else {
                $errors[] = 'Ett fel uppstod när inlägget skulle uppdateras.';
            }
        } catch (PDOException $e) {
            error_log("Update Post Error: " . $e->getMessage());
            $errors[] = 'Databasfel. Kan inte uppdatera inlägg just nu.';
        }
    }

}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redigera inlägg - Admin</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
    <h1>Redigera blogginlägg</h1>
    <p><a href="index.php">&laquo; Tillbaka till Admin Dashboard</a></p>

    <?php if (!empty($errors)): ?>
        <?php $error_title = "Inlägget kunde inte uppdateras:"; ?>
        <?php include 'includes/view-errors.php' ?>
    <?php endif; ?>

    <?php if ($post): ?>
        <form action="edit_post.php?id=<?php echo $post_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nuvarande Bild:</label>
                <?php if ($current_image_path): ?>
                    <div>
                        <img src="<?= htmlspecialchars(BASE_URL . '/' . $current_image_path); ?>" alt="">
                    </div>
                    <label><input type="checkbox" name="delete_image" value="1"> Ta bort nuvarande bild</label>
                <?php else: ?>
                    <p>Ingen bild är uppladdad.</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image">Ladda upp ny bild (valfritt, ersätter nuvarande):</label>
                <input type="file" id="image" name="image" accept="image/jpeg, image/png, image/gif">
            </div>


            <div class="form-group">
                <label for="title">Titel:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>
            <div class="form-group">
                <label for="body">Innehåll:</label>
                <textarea id="body" name="body" required><?php echo htmlspecialchars($body); ?></textarea>
            </div>
            <button type="submit">Uppdatera inlägg</button>
        </form>
    <?php endif; ?>
</body>
</html>