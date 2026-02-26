<?php
// Run this script from your browser after placing the project in XAMPP's htdocs.
// It executes the SQL in db/init.sql using MySQL root credentials.

// If your XAMPP root user has a password, update the $rootPass variable below.
$rootUser = 'root';
$rootPass = 'root123';
$rootHost = '127.0.0.1';
$rootPort = 3307; // XAMPP MySQL port (not default 3306)

$sqlFile = __DIR__ . '/db/init.sql';
if (!file_exists($sqlFile)) {
    die('SQL file not found: ' . $sqlFile);
}

$sql = file_get_contents($sqlFile);

$conn = new mysqli($rootHost, $rootUser, $rootPass, '', $rootPort);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if ($conn->multi_query($sql)) {
    // consume results
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());

    echo '<h2>Success</h2>';
    echo '<p>Database <strong>internships_app</strong> and sample users created/updated.</p>';
    echo '<p>Open <a href="/phpmyadmin/">phpMyAdmin</a> and look for the <strong>internships_app</strong> database.</p>';
    echo '<p>If you need to create MySQL accounts with different root credentials, edit <code>setup_db.php</code>.</p>';
} else {
    echo '<h2>Error</h2>';
    echo '<pre>' . htmlspecialchars($conn->error) . '</pre>';
}

$conn->close();
