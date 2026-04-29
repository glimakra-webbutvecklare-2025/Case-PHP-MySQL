<!-- navigering mellan olika filer / sidor -->
<nav>
    <a href="/">Hem</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="admin/index.php">Admin Dashboard</a>
        <a href="logout.php">Logga ut (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
    <?php else: ?>
        <a href="login.php">Logga in</a>
        <a href="register.php">Registrera dig</a>
    <?php endif; ?>
</nav>