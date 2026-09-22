<?php
/**
 * Dashboard: the posts of the logged in user only.
 * This page is the clearest demonstration of session based access
 * control - a guest is redirected, and the query is limited to the
 * posts that belong to the current user.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$userId = current_user_id();

$stmt = $conn->prepare(
    'SELECT post_id, title, class_level, subject, area, salary, status, created_at
     FROM tuition_posts
     WHERE posted_by = ?
     ORDER BY created_at DESC'
);
$stmt->bind_param('i', $userId);
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'My Posts';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>My tuition posts</h1>
    <a class="btn btn-primary" href="post_create.php">+ New post</a>
</div>

<?php if (!$posts): ?>
    <p class="empty">You have not posted any tuition yet.</p>
<?php else: ?>
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Class</th>
                <th>Subject</th>
                <th>Area</th>
                <th>Salary</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($posts as $post): ?>
            <tr>
                <td><a href="post_details.php?id=<?= (int) $post['post_id'] ?>"><?= e($post['title']) ?></a></td>
                <td><?= e($post['class_level']) ?></td>
                <td><?= e($post['subject']) ?></td>
                <td><?= e($post['area']) ?></td>
                <td><?= (int) $post['salary'] ?></td>
                <td>
                    <span class="badge <?= $post['status'] === 'open' ? 'badge-open' : 'badge-closed' ?>">
                        <?= e(ucfirst($post['status'])) ?>
                    </span>
                </td>
                <td class="actions">
                    <a class="btn btn-small" href="post_edit.php?id=<?= (int) $post['post_id'] ?>">Edit</a>
                    <form method="post" action="post_delete.php" class="inline-form"
                          onsubmit="return confirm('Delete this tuition post permanently?');">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="id" value="<?= (int) $post['post_id'] ?>">
                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
