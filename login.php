<?php
/**
 * Login page. Verifies the submitted password against the stored
 * hash with password_verify() and starts an authenticated session.
 */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$email  = '';

if (get_str('msg') === 'login_required') {
    $errors[] = 'Please log in to view that page.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email    = post_str('email');
    $password = (string) ($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $errors[] = 'Please enter your email and password.';
    }

    if (!$errors) {
        $stmt = $conn->prepare(
            'SELECT user_id, full_name, role, password_hash FROM users WHERE email = ?'
        );
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // One generic message for both cases, so the form does not
        // reveal whether an email exists in the database.
        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);           // prevents session fixation
            $_SESSION['user_id']   = (int) $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            set_flash('success', 'Welcome back, ' . $user['full_name'] . '!');
            header('Location: index.php');
            exit;
        }
        $errors[] = 'Invalid email or password.';
    }
}

$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card form-card">
    <h1>Log in</h1>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" novalidate data-validate="login">
        <?php csrf_field(); ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn btn-primary">Log in</button>
    </form>

    <p class="muted">No account yet? <a href="register.php">Register here</a>.</p>
    <p class="muted demo-box">
        Demo accounts (password <code>Pass@123</code>):<br>
        guardian@demo.com &nbsp;|&nbsp; tutor@demo.com
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
