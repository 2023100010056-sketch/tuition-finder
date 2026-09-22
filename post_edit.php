<?php
/**
 * Update a tuition post (the U of CRUD). Login required, and the
 * ownership check makes sure one user cannot edit another user's post.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = (int) (($_SERVER['REQUEST_METHOD'] === 'POST') ? post_str('id') : get_str('id'));
if ($id <= 0) {
    http_response_code(400);
    die('Invalid post id.');
}

// ---- load the post and verify ownership ----------------------------
$stmt = $conn->prepare('SELECT * FROM tuition_posts WHERE post_id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    http_response_code(404);
    die('Post not found.');
}
if ((int) $post['posted_by'] !== current_user_id()) {
    http_response_code(403);
    die('You are not allowed to edit this post.');
}

$errors = [];
$old = [
    'title'         => $post['title'],
    'class_level'   => $post['class_level'],
    'subject'       => $post['subject'],
    'area'          => $post['area'],
    'salary'        => (string) $post['salary'],
    'days_per_week' => (string) $post['days_per_week'],
    'medium'        => $post['medium'],
    'description'   => (string) $post['description'],
];
$status = $post['status'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old = [
        'title'         => post_str('title'),
        'class_level'   => post_str('class_level'),
        'subject'       => post_str('subject'),
        'area'          => post_str('area'),
        'salary'        => post_str('salary'),
        'days_per_week' => post_str('days_per_week'),
        'medium'        => post_str('medium'),
        'description'   => post_str('description'),
    ];
    $status = post_str('status');

    if (mb_strlen($old['title']) < 5) {
        $errors[] = 'Title must be at least 5 characters long.';
    }
    if (!in_array($old['class_level'], CLASS_LEVELS, true)) {
        $errors[] = 'Please select a valid class level.';
    }
    if (!in_array($old['subject'], SUBJECTS, true)) {
        $errors[] = 'Please select a valid subject.';
    }
    if (mb_strlen($old['area']) < 2) {
        $errors[] = 'Please enter the area.';
    }
    if (!ctype_digit($old['salary']) || (int) $old['salary'] < 500 || (int) $old['salary'] > 100000) {
        $errors[] = 'Salary must be a number between 500 and 100000.';
    }
    if (!ctype_digit($old['days_per_week']) || (int) $old['days_per_week'] < 1 || (int) $old['days_per_week'] > 7) {
        $errors[] = 'Days per week must be between 1 and 7.';
    }
    if (!in_array($old['medium'], MEDIUMS, true)) {
        $errors[] = 'Please select a valid medium.';
    }
    if (!in_array($status, ['open', 'closed'], true)) {
        $errors[] = 'Please select a valid status.';
    }

    if (!$errors) {
        $salary = (int) $old['salary'];
        $days   = (int) $old['days_per_week'];
        $userId = current_user_id();

        // posted_by is part of the WHERE clause as a second safety net.
        $stmt = $conn->prepare(
            'UPDATE tuition_posts
             SET title = ?, class_level = ?, subject = ?, area = ?, salary = ?,
                 days_per_week = ?, medium = ?, description = ?, status = ?
             WHERE post_id = ? AND posted_by = ?'
        );
        $stmt->bind_param(
            'ssssiisssii',
            $old['title'], $old['class_level'], $old['subject'], $old['area'],
            $salary, $days, $old['medium'], $old['description'], $status,
            $id, $userId
        );
        $stmt->execute();
        $stmt->close();

        set_flash('success', 'The tuition post has been updated.');
        header('Location: my_posts.php');
        exit;
    }
}

$page_title = 'Edit Tuition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card form-card">
    <h1>Edit tuition post</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="post_edit.php" novalidate data-validate="post">
        <?php csrf_field(); ?>
        <input type="hidden" name="id" value="<?= (int) $id ?>">
        <?php include __DIR__ . '/includes/post_form_fields.php'; ?>

        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Open (still looking)</option>
            <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Closed (tutor found)</option>
        </select>

        <button type="submit" class="btn btn-primary">Save changes</button>
        <a class="btn btn-small" href="my_posts.php">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
