<?php
require_once __DIR__ . '/config/db_config.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
if ($conn->connect_error) {
    die('Connection error: ' . $conn->connect_error);
}

echo '<h2>Users in database:</h2>';
$res = $conn->query("SELECT id, name, email, role, password FROM users");
if ($res) {
    echo '<table border="1" cellpadding="10"><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Password Hash</th></tr>';
    while ($r = $res->fetch_assoc()) {
        echo '<tr><td>' . $r['id'] . '</td><td>' . $r['name'] . '</td><td>' . $r['email'] . '</td><td>' . $r['role'] . '</td><td><code>' . substr($r['password'], 0, 50) . '...</code></td></tr>';
    }
    echo '</table>';
} else {
    echo 'Error: ' . $conn->error;
}

echo '<h2>Test password_verify:</h2>';
$testPassword = 'password123';
$testHash = '$2y$10$f3M4hC0XvtN2xJmj7LdZVeLKVF4JJ/2To6Vgj/QoF.70.MAyDNDze';
echo '<p>Testing: password_verify("' . $testPassword . '", "' . $testHash . '") = ';
echo password_verify($testPassword, $testHash) ? '<strong style="color:green">TRUE ✓</strong>' : '<strong style="color:red">FALSE ✗</strong>';
echo '</p>';

$conn->close();
?>
