<?php
/**
 * Admin: list every user and delete an account if necessary.
 * Deleting a user also removes that user's posts, because the
 * foreign key in tuition_posts is declared ON DELETE CASCADE.
 */
require_once __DIR__ . '/includes/functions.php';
require_admin();

// ---- delete action -------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $targetId = (int) post_str('user_id');

    if ($targetId === current_user_id()) {
        set_flash('error', 'You cannot delete your own admin account.');
    } elseif ($targetId <= 0) {
        set_flash('error', 'Invalid user.');
    } else {
        // An admin account may not be deleted from the web interface.
        $stmt = $conn->prepare('DELETE FROM users WHERE user_id = ? AND role <> "admin"');
        $stmt->bind_param('i', $targetId);
        $stmt->execute();
        $removed = $stmt->affected_rows;
        $stmt->close();

        if ($removed > 0) {
            set_flash('success', 'The user and all of their posts have been deleted.');
        } else {
            set_flash('error', 'That user could not be deleted.');
        }
    }

    header('Location: admin_users.php');
    exit;
}

// ---- optional search over users ------------------------------------
$keyword = get_str('keyword');

$sql = 'SELECT u.user_id, u.full_name, u.email, u.phone, u.role, u.created_at,
               (SELECT COUNT(*) FROM tuition_posts p WHERE p.posted_by = u.user_id) AS post_count
        FROM users u';
$params = [];
$types  = '';

if ($keyword !== '') {
    $sql .= ' WHERE u.full_name LIKE ? OR u.email LIKE ?';
    $like = '%' . $keyword . '%';
    $types = 'ss';
    $params = [$like, $like];
}
$sql .= ' ORDER BY u.created_at DESC';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Manage Users';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <h1>Manage users</h1>
    <a class="btn" href="admin_dashboard.php">&larr; Dashboard</a>
</div>

<form class="card filter-form" method="get" action="admin_users.php">
    <label for="keyword">Search by name or email</label>
    <div class="filter-row">
        <input type="text" id="keyword" name="keyword" value="<?= e($keyword) ?>" placeholder="e.g. demo">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <a class="btn btn-small" href="admin_users.php">Reset</a>
</form>

<p class="muted"><?= count($users) ?> user<?= count($users) === 1 ? '' : 's' ?>.</p>

<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Posts</th>
            <th>Joined</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= e($user['full_name']) ?></td>
            <td><?= e($user['email']) ?></td>
            <td><?= e($user['phone']) ?></td>
            <td><span class="badge badge-role"><?= e(ucfirst($user['role'])) ?></span></td>
            <td><?= (int) $user['post_count'] ?></td>
            <td><?= e(date('d M Y', strtotime($user['created_at']))) ?></td>
            <td class="actions">
                <?php if ($user['role'] === 'admin'): ?>
                    <span class="muted">&mdash;</span>
                <?php else: ?>
                    <form method="post" action="admin_users.php" class="inline-form"
                          onsubmit="return confirm('Delete this user and all of their posts?');">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>">
                        <button type="submit" class="btn btn-small btn-danger">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
