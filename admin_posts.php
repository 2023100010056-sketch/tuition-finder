<?php
/**
 * Admin: moderate every tuition post on the site.
 * The admin can close, reopen or delete any post, no matter
 * which user created it.
 */
require_once __DIR__ . '/includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $postId = (int) post_str('post_id');
    $action = post_str('action');

    if ($postId <= 0) {
        set_flash('error', 'Invalid post.');
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare('DELETE FROM tuition_posts WHERE post_id = ?');
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'The post has been deleted.');
    } elseif ($action === 'open' || $action === 'closed') {
        $stmt = $conn->prepare('UPDATE tuition_posts SET status = ? WHERE post_id = ?');
        $stmt->bind_param('si', $action, $postId);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'The status of the post has been updated.');
    } else {
        set_flash('error', 'Unknown action.');
    }

    header('Location: admin_posts.php');
    exit;
}

// ---- filters --------------------------------------------------------
$keyword = get_str('keyword');
$status  = get_str('status');

$sql = 'SELECT p.post_id, p.title, p.class_level, p.subject, p.area, p.salary,
               p.status, p.created_at, u.full_name, u.email
        FROM tuition_posts p
        JOIN users u ON u.user_id = p.posted_by
        WHERE 1 = 1';
$types  = '';
$params = [];

if ($keyword !== '') {
    $sql .= ' AND (p.title LIKE ? OR p.area LIKE ? OR u.full_name LIKE ?)';
    $like = '%' . $keyword . '%';
    $types .= 'sss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($status === 'open' || $status === 'closed') {
    $sql .= ' AND p.status = ?';
    $types .= 's';
    $params[] = $status;
}
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Manage Posts';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Manage tuition posts</h1>
    <a class="btn" href="admin_dashboard.php">&larr; Dashboard</a>
</div>

<form class="card filter-form" method="get" action="admin_posts.php">
    <div class="filter-row">
        <div>
            <label for="keyword">Keyword</label>
            <input type="text" id="keyword" name="keyword" value="<?= e($keyword) ?>"
                   placeholder="title, area or poster">
        </div>
        <div>
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="">Any</option>
                <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open</option>
                <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed</option>
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <a class="btn btn-small" href="admin_posts.php">Reset</a>
</form>

<p class="muted"><?= count($posts) ?> post<?= count($posts) === 1 ? '' : 's' ?>.</p>

<?php if (!$posts): ?>
    <p class="empty">No post matches this filter.</p>
<?php else: ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Title</th>
            <th>Subject</th>
            <th>Area</th>
            <th>Salary</th>
            <th>Posted by</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($posts as $post): ?>
        <tr>
            <td><a href="post_details.php?id=<?= (int) $post['post_id'] ?>"><?= e($post['title']) ?></a></td>
            <td><?= e($post['subject']) ?></td>
            <td><?= e($post['area']) ?></td>
            <td><?= (int) $post['salary'] ?></td>
            <td><?= e($post['full_name']) ?></td>
            <td>
                <span class="badge <?= $post['status'] === 'open' ? 'badge-open' : 'badge-closed' ?>">
                    <?= e(ucfirst($post['status'])) ?>
                </span>
            </td>
            <td class="actions">
                <form method="post" action="admin_posts.php" class="inline-form">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="post_id" value="<?= (int) $post['post_id'] ?>">
                    <input type="hidden" name="action" value="<?= $post['status'] === 'open' ? 'closed' : 'open' ?>">
                    <button type="submit" class="btn btn-small">
                        <?= $post['status'] === 'open' ? 'Close' : 'Reopen' ?>
                    </button>
                </form>
                <form method="post" action="admin_posts.php" class="inline-form"
                      onsubmit="return confirm('Delete this post permanently?');">
                    <?php csrf_field(); ?>
                    <input type="hidden" name="post_id" value="<?= (int) $post['post_id'] ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="btn btn-small btn-danger">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
