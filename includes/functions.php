<?php
/**
 * Shared helper functions: session handling, authentication guard,
 * CSRF protection, output escaping and input validation.
 *
 * Every page includes this file first.
 */

require_once __DIR__ . '/../config/db.php';

// ---------------------------------------------------------------
// Session
// ---------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Is somebody logged in right now? */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

/** The id of the logged in user, or 0 for a guest. */
function current_user_id(): int
{
    return (int) ($_SESSION['user_id'] ?? 0);
}

/** The role of the logged in user: guardian, tutor, admin or '' for a guest. */
function current_role(): string
{
    return (string) ($_SESSION['role'] ?? '');
}

/** Is the logged in user a site administrator? */
function is_admin(): bool
{
    return is_logged_in() && current_role() === 'admin';
}

/**
 * Access control: pages that require a login call this first.
 * A guest is redirected to the login page and never sees the content.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php?msg=login_required');
        exit;
    }
}

/**
 * Access control for the admin area. A guest is sent to the login
 * page; a logged in but ordinary user is refused with HTTP 403.
 */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        http_response_code(403);
        die('Access denied. This page is for administrators only.');
    }
}

// ---------------------------------------------------------------
// Output escaping (protects against XSS)
// ---------------------------------------------------------------
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// ---------------------------------------------------------------
// CSRF protection
// ---------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Prints the hidden input that every POST form must contain. */
function csrf_field(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Stops the request if the submitted token does not match the session token. */
function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        die('Invalid request (CSRF token mismatch).');
    }
}

// ---------------------------------------------------------------
// Input validation helpers
// ---------------------------------------------------------------
/** Trimmed POST value as a string. */
function post_str(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

/** Trimmed GET value as a string. */
function get_str(string $key): string
{
    return trim((string) ($_GET[$key] ?? ''));
}

/** Bangladeshi mobile number: 11 digits starting with 01. */
function is_valid_phone(string $phone): bool
{
    return (bool) preg_match('/^01[3-9]\d{8}$/', $phone);
}

/**
 * Password policy: at least 8 characters, one letter and one digit.
 */
function is_strong_password(string $password): bool
{
    return strlen($password) >= 8
        && preg_match('/[A-Za-z]/', $password)
        && preg_match('/\d/', $password);
}

// ---------------------------------------------------------------
// Flash messages (shown once, on the next page load)
// ---------------------------------------------------------------
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $class = $flash['type'] === 'error' ? 'alert alert-error' : 'alert alert-success';
        echo '<div class="' . $class . '">' . e($flash['message']) . '</div>';
    }
}

// ---------------------------------------------------------------
// Fixed option lists (also used to validate select inputs)
// ---------------------------------------------------------------
const CLASS_LEVELS = ['Class 1-5', 'Class 6', 'Class 7', 'Class 8', 'Class 9', 'Class 10', 'HSC', 'Admission', 'University'];
const SUBJECTS     = ['Mathematics', 'English', 'Physics', 'Chemistry', 'Biology', 'ICT', 'Accounting', 'Bangla', 'All Subjects'];
const MEDIUMS      = ['Bangla', 'English', 'Both'];
