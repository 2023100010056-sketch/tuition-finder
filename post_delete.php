<?php
/**
 * Delete a tuition post (the D of CRUD).
 *
 * Only accepts POST (so a link cannot delete anything), requires a
 * valid CSRF token, and deletes only when the post belongs to the
 * logged in user.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed.');
}

verify_csrf();

$id = (int) post_str('id');
if ($id <= 0) {
    http_response_code(400);
    die('Invalid post id.');
}

$userId = current_user_id();
$stmt = $conn->prepare('DELETE FROM tuition_posts WHERE post_id = ? AND posted_by = ?');
$stmt->bind_param('ii', $id, $userId);
$stmt->execute();
$deleted = $stmt->affected_rows;
$stmt->close();

if ($deleted > 0) {
    set_flash('success', 'The tuition post has been deleted.');
} else {
    set_flash('error', 'That post could not be deleted (it may not be yours).');
}

header('Location: my_posts.php');
exit;
