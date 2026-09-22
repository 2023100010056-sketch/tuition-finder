<?php
/**
 * Admin dashboard: site-wide statistics.
 * Only reachable by a user whose role is 'admin'.
 */
require_once __DIR__ . '/includes/functions.php';
require_admin();

$stats = $conn->query(
    'SELECT
        (SELECT COUNT(*) FROM users)                                   AS total_users,
        (SELECT COUNT(*) FROM users WHERE role = "guardian")           AS guardians,
        (SELECT COUNT(*) FROM users WHERE role = "tutor")              AS tutors,
        (SELECT COUNT(*) FROM tuition_posts)                           AS total_posts,
        (SELECT COUNT(*) FROM tuition_posts WHERE status = "open")     AS open_posts,
        (SELECT COUNT(*) FROM tuition_posts WHERE status = "closed")   AS closed_posts,
        (SELECT COUNT(DISTINCT area) FROM tuition_posts)               AS areas,
        (SELECT IFNULL(ROUND(AVG(salary)), 0) FROM tuition_posts)      AS avg_salary'
)->fetch_assoc();

// Most requested subjects
$bySubject = $conn->query(
    'SELECT subject, COUNT(*) AS total
     FROM tuition_posts
     GROUP BY subject
     ORDER BY total DESC, subject ASC
     LIMIT 6'
)->fetch_all(MYSQLI_ASSOC);

// Latest five posts across the whole site
$latest = $conn->query(
    'SELECT p.post_id, p.title, p.area, p.salary, p.status, p.created_at, u.full_name
     FROM tuition_posts p
     JOIN users u ON u.user_id = p.posted_by
     ORDER BY p.created_at DESC
     LIMIT 5'
)->fetch_all(MYSQLI_ASSOC);

$maxSubject = $bySubject ? max(array_column($bySubject, 'total')) : 1;

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Admin dashboard</h1>
        <p class="muted">Overview of every user and tuition post on the site.</p>
    </div>
    <div>
        <a class="btn" href="admin_users.php">Manage users</a>
        <a class="btn" href="admin_posts.php">Manage posts</a>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-value"><?= (int) $stats['total_users'] ?></span>
        <span class="stat-label">Registered users</span>
        <span class="muted"><?= (int) $stats['guardians'] ?> guardians &middot; <?= (int) $stats['tutors'] ?> tutors</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= (int) $stats['total_posts'] ?></span>
        <span class="stat-label">Tuition posts</span>
        <span class="muted"><?= (int) $stats['open_posts'] ?> open &middot; <?= (int) $stats['closed_posts'] ?> closed</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= (int) $stats['areas'] ?></span>
        <span class="stat-label">Areas covered</span>
        <span class="muted">Distinct locations</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= (int) $stats['avg_salary'] ?></span>
        <span class="stat-label">Average salary (BDT)</span>
        <span class="muted">Across all posts</span>
    </div>
</div>

<div class="admin-columns">
    <section class="card">
        <h2>Most requested subjects</h2>
        <?php if (!$bySubject): ?>
            <p class="muted">No posts yet.</p>
        <?php else: ?>
            <ul class="bar-list">
                <?php foreach ($bySubject as $row): ?>
                    <li>
                        <span class="bar-label"><?= e($row['subject']) ?></span>
                        <span class="bar-track">
                            <span class="bar-fill"
                                  style="width: <?= (int) round($row['total'] / $maxSubject * 100) ?>%"></span>
                        </span>
                        <span class="bar-value"><?= (int) $row['total'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Latest posts</h2>
        <?php if (!$latest): ?>
            <p class="muted">No posts yet.</p>
        <?php else: ?>
            <ul class="plain-list">
                <?php foreach ($latest as $row): ?>
                    <li>
                        <a href="post_details.php?id=<?= (int) $row['post_id'] ?>"><?= e($row['title']) ?></a>
                        <span class="badge <?= $row['status'] === 'open' ? 'badge-open' : 'badge-closed' ?>">
                            <?= e(ucfirst($row['status'])) ?>
                        </span>
                        <br>
                        <small class="muted">
                            <?= e($row['full_name']) ?> &middot; <?= e($row['area']) ?> &middot;
                            <?= (int) $row['salary'] ?> BDT &middot;
                            <?= e(date('d M Y', strtotime($row['created_at']))) ?>
                        </small>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
