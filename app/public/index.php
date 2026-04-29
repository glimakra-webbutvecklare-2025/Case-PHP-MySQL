<?php
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/Post.php';

$posts = [];
$fetch_error = null;

try {
    $post_model = new Post(connect_db());
    $posts = $post_model->showAll();
} catch (PDOException $e) {
    error_log("Index Page Error: " . $e->getMessage());
    $fetch_error = "Kunde inte hämta blogginlägg just nu. Försök igen senare.";
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enkel Blogg</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <nav>
        <a href="index.php">Hem</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="admin/index.php">Admin Dashboard</a>
            <a href="logout.php">Logga ut (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
        <?php else: ?>
            <a href="login.php">Logga in</a>
            <a href="register.php">Registrera dig</a>
        <?php endif; ?>
    </nav>

    <h1>Välkommen till Bloggen!</h1>

    <?php if (isset($_GET['logged_out']) && $_GET['logged_out'] === 'success'): ?>
        <p class="success-message">Du har loggats ut.</p>
    <?php endif; ?>

    <?php if ($fetch_error): ?>
        <p class="error-message"><?php echo htmlspecialchars($fetch_error); ?></p>
    <?php elseif (empty($posts)): ?>
        <p>Det finns inga blogginlägg ännu.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php include 'includes/view-post.php' ?>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
