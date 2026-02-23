<?php
require_once __DIR__ . '/config/db_config.php';

header('Content-Type: text/html; charset=utf-8');
echo '<h2>Debug MySQL connection</h2>';
echo '<p><strong>Using config:</strong> DB_HOST=' . DB_HOST . ' DB_USER=' . DB_USER . ' DB_NAME=' . DB_NAME . '</p>';

$conn = @new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    echo '<p style="color:crimson">Connection error: ' . htmlspecialchars($conn->connect_error) . '</p>';
    exit;
}

echo '<p><strong>Client host info:</strong> ' . htmlspecialchars($conn->host_info) . '</p>';

$res = $conn->query("SELECT USER() AS user, CURRENT_USER() AS current_user");
if ($res) {
    $r = $res->fetch_assoc();
    echo '<p><strong>USER():</strong> ' . htmlspecialchars($r['user']) . ' &nbsp; <strong>CURRENT_USER():</strong> ' . htmlspecialchars($r['current_user']) . '</p>';
}

echo '<h3>Databases on this server</h3>';
$dbl = $conn->query('SHOW DATABASES');
if ($dbl) {
    echo '<ul>';
    while ($d = $dbl->fetch_assoc()) {
        echo '<li>' . htmlspecialchars($d['Database']) . '</li>';
    }
    echo '</ul>';
} else {
    echo '<p>No databases or insufficient privileges: ' . htmlspecialchars($conn->error) . '</p>';
}

echo '<h3>Last users in `internships_app.users` (if accessible)</h3>';
$users = $conn->query('SELECT id, name, email, role, created_at FROM internships_app.users ORDER BY id DESC LIMIT 10');
if ($users) {
    echo '<pre>' . htmlspecialchars(print_r($users->fetch_all(MYSQLI_ASSOC), true)) . '</pre>';
} else {
    echo '<p>Could not read table: ' . htmlspecialchars($conn->error) . '</p>';
}

$conn->close();

echo '<p>Compare these values with what you use to log into phpMyAdmin (user/password). If phpMyAdmin connects with a different MySQL server or different credentials, it may show different databases.</p>';
