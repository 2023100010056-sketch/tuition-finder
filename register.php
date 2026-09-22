<?php
/**
 * User registration.
 * Server side validation + password hashing with password_hash().
 */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$old    = ['full_name' => '', 'email' => '', 'phone' => '', 'role' => 'guardian'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $full_name = post_str('full_name');
    $email     = post_str('email');
    $phone     = post_str('phone');
    $role      = post_str('role');
    $password  = (string) ($_POST['password'] ?? '');
    $confirm   = (string) ($_POST['confirm_password'] ?? '');

    $old = compact('full_name', 'email', 'phone', 'role');

    // ---- validation ------------------------------------------------
    if ($full_name === '' || mb_strlen($full_name) < 3) {
        $errors[] = 'Full name must be at least 3 characters long.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (!is_valid_phone($phone)) {
        $errors[] = 'Phone must be a valid 11 digit Bangladeshi number (e.g. 01712345678).';
    }
    if (!in_array($role, ['guardian', 'tutor'], true)) {
        $errors[] = 'Please choose a valid role.';
    }
    if (!is_strong_password($password)) {
        $errors[] = 'Password must be at least 8 characters and contain both letters and digits.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Password and confirm password do not match.';
    }

    // ---- duplicate email check (prepared statement) -----------------
    if (!$errors) {
        $stmt = $conn->prepare('SELECT user_id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = 'This email is already registered. Please log in instead.';
        }
        $stmt->close();
    }

    // ---- insert -----------------------------------------------------
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare(
            'INSERT INTO users (full_name, email, phone, role, password_hash) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('sssss', $full_name, $email, $phone, $role, $hash);
        $stmt->execute();
        $stmt->close();

        set_flash('success', 'Registration successful. You can log in now.');
        header('Location: login.php');
        exit;
    }
}

$page_title = 'Register';
require_once __DIR__ . '/includes/header.php';
?>

<div class="card form-card">
    <h1>Create an account</h1>
    <p class="muted">Guardians can post tuitions, tutors can browse and contact them.</p>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php" novalidate data-validate="register">
        <?php csrf_field(); ?>

        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" value="<?= e($old['full_name']) ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required>

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" value="<?= e($old['phone']) ?>"
               placeholder="01712345678" required>

        <label for="role">I am a</label>
        <select id="role" name="role" required>
            <option value="guardian" <?= $old['role'] === 'guardian' ? 'selected' : '' ?>>Guardian / Student</option>
            <option value="tutor" <?= $old['role'] === 'tutor' ? 'selected' : '' ?>>Tutor</option>
        </select>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <small class="muted">At least 8 characters, with letters and digits.</small>

        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit" class="btn btn-primary">Register</button>
    </form>

    <p class="muted">Already registered? <a href="login.php">Log in here</a>.</p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
