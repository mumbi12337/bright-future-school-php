<?php
// 1. Include the database connection we set up earlier
require_once(__DIR__ . '/../includes/db.php');

try {
    echo "Starting migration...<br>";

    // 2. Point to your schema file
    $schemaFile = __DIR__ . '/schema.sql';

    if (!file_exists($schemaFile)) {
        die("Error: schema.sql not found at " . $schemaFile);
    }

    // 3. Read the SQL content
    $sql = file_get_contents($schemaFile);

    // 4. Execute the SQL against the Railway Postgres DB
    $pdo->exec($sql);

    echo "<b>Success!</b> Your database tables have been created.<br>";
    echo "<a href='../index.php'>Go to Homepage</a>";

} catch (PDOException $e) {
    echo "<b>Migration Failed:</b> " . $e->getMessage();
}