<?php
/**
 * Single tuition post.
 *
 * The contact details of the poster are only shown to a logged in
 * user - a guest is asked to log in first. The owner of the post
 * also sees the Edit and Delete buttons here.
 */
require_once __DIR__ . '/includes/functions.php';

$id = (int) get_str('id');
if ($id <= 0) {
    http_response_code(404);
    die('Invalid post id.');
}

$stmt = $conn->prepare(
    'SELECT p.*, u.full_name, u.email, u.phone
     FROM tuition_posts p
     JOIN users u ON u.user_id = p.posted_by
     WHERE p.post_id = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    $page_title = 'Not found';
    require_once __DIR__ . '/includes/header.php';
    echo '<p class="empty">This tuition post does not exist or has been deleted.</p>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$is_owner   = is_logged_in() && current_user_id() === (int) $post['posted_by'];
$page_title = $post['title'];
require_once __DIR__ . '/includes/header.php';
?>

<article class="card details">
    <h1><?= e($post['title']) ?></h1>
    <p class="badge <?= $post['status'] === 'open' ? 'badge-open' : 'badge-closed' ?>">
        <?= e(ucfirst($post['status'])) ?>
    </p>

    <table class="details-table">
        <tr><th>Class</th><td><?= e($post['class_level']) ?></td></tr>
        <tr><th>Subject</th><td><?= e($post['subject']) ?></td></tr>
        <tr><th>Area</th><td><?= e($post['area']) ?></td></tr>
        <tr><th>Salary</th><td><?= (int) $post['salary'] ?> BDT per month</td></tr>
        <tr><th>Days per week</th><td><?= (int) $post['days_per_week'] ?></td></tr>
        <tr><th>Medium</th><td><?= e($post['medium']) ?></td></tr>
        <tr><th>Posted by</th><td><?= e($post['full_name']) ?></td></tr>
        <tr><th>Posted on</th><td><?= e(date('d M Y, h:i A', strtotime($post['created_at']))) ?></td></tr>
    </table>

    <?php if (!empty($post['description'])): ?>
        <h2>Description</h2>
        <p><?= nl2br(e($post['description'])) ?></p>
    <?php endif; ?>

    <h2>Contact</h2>
    <?php if (is_logged_in()): ?>
        <p>
            Phone: <strong><?= e($post['phone']) ?></strong><br>
            Email: <strong><?= e($post['email']) ?></strong>
        </p>
    <?php else: ?>
        <p class="muted">
            <a href="login.php">Log in</a> to see the contact details of the person who posted this.
        </p>
    <?php endif; ?>

    <?php if ($is_owner): ?>
        <div class="owner-actions">
            <a class="btn" href="post_edit.php?id=<?= (int) $post['post_id'] ?>">Edit</a>
            <form method="post" action="post_delete.php" class="inline-form"
                  onsubmit="return confirm('Delete this tuition post permanently?');">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int) $post['post_id'] ?>">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    <?php endif; ?>

    <p><a href="browse.php">&larr; Back to all tuitions</a></p>
</article>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
