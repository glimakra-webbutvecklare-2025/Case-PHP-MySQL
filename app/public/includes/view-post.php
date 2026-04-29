<article class="post-summary">
    <?php if (!empty($post['image_path'])): ?>
        <img src="<?php echo htmlspecialchars(BASE_URL . '/' . $post['image_path']); ?>"
                alt="Inläggsbild" class="post-image-list">
    <?php endif; ?>
    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
    <div class="post-meta">
        Publicerad: <?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?>
        av <?php echo htmlspecialchars($post['username']); ?>
    </div>
    <p>
        <?php
        $summary = htmlspecialchars($post['body']);
        if (strlen($summary) > 200) {
            $summary = substr($summary, 0, 200) . '...';
        }
        echo nl2br($summary);
        ?>
    </p>
    <a href="post.php?id=<?php echo $post['id']; ?>">Läs mer &raquo;</a>
    <div style="clear: both;"></div>
</article>