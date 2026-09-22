<?php
/**
 * Database connection (MySQLi, object oriented style).
 *
 * Change the four constants below to match your own environment.
 * ----------------------------------------------------------------
 * Local XAMPP default : host=localhost, user=root, pass='' , db=tuition_finder
 * InfinityFree        : copy the values shown in the hosting control panel
 * ----------------------------------------------------------------
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tuition_finder');

// Throw exceptions on MySQL errors instead of silently continuing.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Never show raw database errors to the visitor.
    die('Database connection failed. Please check config/db.php.');
}
