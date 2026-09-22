<?php
/**
 * Browse / search page.
 *
 * Supports a keyword search (title, subject, area) plus filters on
 * class level, subject, medium and a maximum salary. Every filter is
 * added to the SQL as a bound parameter, never as string concatenation,
 * so the page is safe against SQL injection.
 */
require_once __DIR__ . '/includes/functions.php';

$keyword  = get_str('keyword');
$class    = get_str('class_level');
$subject  = get_str('subject');
$medium   = get_str('medium');
$maxSalary = get_str('max_salary');

// ---- build the query dynamically, with placeholders -----------------
$sql    = 'SELECT p.post_id, p.title, p.class_level, p.subject, p.area, p.salary,
                  p.days_per_week, p.medium, p.status, p.created_at, u.full_name
           FROM tuition_posts p
           JOIN users u ON u.user_id = p.posted_by
           WHERE p.status = "open"';
$types  = '';
$params = [];

if ($keyword !== '') {
    $sql .= ' AND (p.title LIKE ? OR p.subject LIKE ? OR p.area LIKE ?)';
    $like = '%' . $keyword . '%';
    $types .= 'sss';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($class !== '' && in_array($class, CLASS_LEVELS, true)) {
    $sql .= ' AND p.class_level = ?';
    $types .= 's';
    $params[] = $class;
}
if ($subject !== '' && in_array($subject, SUBJECTS, true)) {
    $sql .= ' AND p.subject = ?';
    $types .= 's';
    $params[] = $subject;
}
if ($medium !== '' && in_array($medium, MEDIUMS, true)) {
    $sql .= ' AND p.medium = ?';
    $types .= 's';
    $params[] = $medium;
}
if ($maxSalary !== '' && ctype_digit($maxSalary)) {
    $sql .= ' AND p.salary <= ?';
    $types .= 'i';
    $params[] = (int) $maxSalary;
}

$sql .= ' ORDER BY p.created_at DESC';

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Browse Tuitions';
require_once __DIR__ . '/includes/header.php';
?>

<h1>Browse tuitions</h1>

<form class="card filter-form" method="get" action="browse.php">
    <div class="filter-row">
        <div>
            <label for="keyword">Keyword</label>
            <input type="text" id="keyword" name="keyword" value="<?= e($keyword) ?>"
                   placeholder="title, subject or area">
        </div>
        <div>
            <label for="class_level">Class</label>
            <select id="class_level" name="class_level">
                <option value="">Any</option>
                <?php foreach (CLASS_LEVELS as $option): ?>
                    <option value="<?= e($option) ?>" <?= $class === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="subject">Subject</label>
            <select id="subject" name="subject">
                <option value="">Any</option>
                <?php foreach (SUBJECTS as $option): ?>
                    <option value="<?= e($option) ?>" <?= $subject === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="medium">Medium</label>
            <select id="medium" name="medium">
                <option value="">Any</option>
                <?php foreach (MEDIUMS as $option): ?>
                    <option value="<?= e($option) ?>" <?= $medium === $option ? 'selected' : '' ?>><?= e($option) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="max_salary">Max salary (BDT)</label>
            <input type="number" id="max_salary" name="max_salary" min="0" step="500"
                   value="<?= e($maxSalary) ?>">
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Search</button>
    <a class="btn btn-small" href="browse.php">Reset</a>
</form>

<p class="muted"><?= count($posts) ?> result<?= count($posts) === 1 ? '' : 's' ?> found.</p>

<?php if (!$posts): ?>
    <p class="empty">No tuition matches your search. Try removing a filter.</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($posts as $post): ?>
            <article class="card post-card">
                <h3><a href="post_details.php?id=<?= (int) $post['post_id'] ?>"><?= e($post['title']) ?></a></h3>
                <ul class="meta">
                    <li><strong>Class:</strong> <?= e($post['class_level']) ?></li>
                    <li><strong>Subject:</strong> <?= e($post['subject']) ?></li>
                    <li><strong>Area:</strong> <?= e($post['area']) ?></li>
                    <li><strong>Salary:</strong> <?= (int) $post['salary'] ?> BDT/month</li>
                    <li><strong>Days:</strong> <?= (int) $post['days_per_week'] ?>/week</li>
                    <li><strong>Medium:</strong> <?= e($post['medium']) ?></li>
                </ul>
                <p class="muted">Posted by <?= e($post['full_name']) ?>
                    on <?= e(date('d M Y', strtotime($post['created_at']))) ?></p>
                <a class="btn btn-small" href="post_details.php?id=<?= (int) $post['post_id'] ?>">View details</a>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
