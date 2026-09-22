<?php
/**
 * Create a tuition post (the C of CRUD). Login required.
 */
require_once __DIR__ . '/includes/functions.php';
require_login();

$errors = [];
$old = [
    'title' => '', 'class_level' => '', 'subject' => '', 'area' => '',
    'salary' => '', 'days_per_week' => '3', 'medium' => 'Bangla', 'description' => '',
];

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
        $errors[] = 'Please enter the area (for example Mirpur, Uttara).';
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
    if (mb_strlen($old['description']) > 1000) {
        $errors[] = 'Description cannot be longer than 1000 characters.';
    }

    if (!$errors) {
        $userId = current_user_id();
        $salary = (int) $old['salary'];
        $days   = (int) $old['days_per_week'];

        $stmt = $conn->prepare(
            'INSERT INTO tuition_posts
             (posted_by, title, class_level, subject, area, salary, days_per_week, medium, description)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'issssiiss',
            $userId, $old['title'], $old['class_level'], $old['subject'],
            $old['area'], $salary, $days, $old['medium'], $old['description']
        );
        $stmt->execute();
        $stmt->close();

        set_flash('success', 'Your tuition post has been published.');
        header('Location: my_posts.php');
        exit;
    }
}

$page_title = 'Post a Tuition';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card form-card">
    <h1>Post a tuition</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="post_create.php" novalidate data-validate="post">
        <?php csrf_field(); ?>
        <?php include __DIR__ . '/includes/post_form_fields.php'; ?>
        <button type="submit" class="btn btn-primary">Publish post</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
