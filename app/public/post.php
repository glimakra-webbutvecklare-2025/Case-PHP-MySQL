<?php
declare(strict_types=1);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once "includes/Post.php";

$post_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$post = null;
$fetch_error = null;

// kontroller om giltigt post id
if ($post_id === false || $post_id <= 0) {
    $fetch_error = "Ogiltigt inläggs-ID.";
} else {
    try {
        $post_model = new Post(connect_db());
        $post = $post_model->showOne($post_id);
        if (!$post) {
            $fetch_error = "Inlägget hittades inte.";
        }
    } catch (PDOException $e) {
        error_log("View Post Error (ID: $post_id): " . $e->getMessage());
        $fetch_error = "Kunde inte hämta blogginlägget just nu.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $post ? htmlspecialchars($post['title']) : 'Inlägg'; ?> - Enkel Blogg</title>
    <link rel="stylesheet" href="styles/style.css">

</head>
<body>

    <?php include 'includes/nav.php' ?>

    <?php if ($fetch_error): ?>
        <p class="error-message"><?php echo htmlspecialchars($fetch_error); ?></p>
    <?php elseif ($post): ?>
        <article class="post-content">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <div class="post-meta">
                Publicerad: <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?>
                av <?php echo htmlspecialchars($post['username']); ?>
                <?php if ($post['created_at'] !== $post['updated_at']): ?>
                    (Senast ändrad: <?php echo date('Y-m-d H:i', strtotime($post['updated_at'])); ?>)
                <?php endif; ?>
            </div>
            <?php if (!empty($post['image_path'])): ?>
                <img src="<?php echo htmlspecialchars(BASE_URL . '/' . $post['image_path']); ?>"
                     alt="<?php echo htmlspecialchars($post['title']); ?>" class="post-image-full">
            <?php endif; ?>
            <div><?php echo nl2br(htmlspecialchars($post['body'])); ?></div>
        </article>
        <p><a href="index.php">&laquo; Tillbaka till alla inlägg</a></p>
    <?php endif; ?>


</body>
</html>