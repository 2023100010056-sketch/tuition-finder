<?php
/**
 * Common page header: <head>, navigation bar and the opening
 * container tag. Pages set $page_title before including this file.
 */
require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? 'Tuition Finder';

// Used to highlight the current item in the navigation bar.
$current_page = basename($_SERVER['PHP_SELF']);

/** Returns class="active" when $file is the page being viewed. */
function nav_active(string $file): string
{
    global $current_page;
    return $current_page === $file ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> | Tuition Finder</title>
    <meta name="description" content="Tuition Finder connects guardians and students with the right tutor in their own area.">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">
        <a class="logo" href="index.php">
            <span class="logo-mark" aria-hidden="true">TF</span>
            <span class="logo-text">Tuition<strong>Finder</strong></span>
        </a>

        <button class="nav-toggle" type="button" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav">
            <a href="index.php"<?= nav_active('index.php') ?>>Home</a>
            <a href="browse.php"<?= nav_active('browse.php') ?>>Browse Tuitions</a>

            <?php if (is_logged_in()): ?>
                <a href="my_posts.php"<?= nav_active('my_posts.php') ?>>My Posts</a>
                <?php if (is_admin()): ?>
                    <a href="admin_dashboard.php"<?= nav_active('admin_dashboard.php') ?>>Admin</a>
                <?php endif; ?>
                <span class="nav-divider" aria-hidden="true"></span>
                <span class="nav-user">Hi, <?= e($_SESSION['full_name'] ?? 'User') ?></span>
                <a class="btn btn-small btn-primary" href="post_create.php">+ Post a Tuition</a>
                <a class="btn btn-small" href="logout.php">Logout</a>
            <?php else: ?>
                <span class="nav-divider" aria-hidden="true"></span>
                <a href="login.php"<?= nav_active('login.php') ?>>Login</a>
                <a class="btn btn-small btn-primary" href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="<?= $current_page === 'index.php' ? 'home-main' : 'container' ?>">
    <?php if ($current_page === 'index.php'): ?>
        <div class="container flash-wrap"><?php show_flash(); ?></div>
    <?php else: ?>
        <?php show_flash(); ?>
    <?php endif; ?>
