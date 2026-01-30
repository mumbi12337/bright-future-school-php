<?php
// 1. Include the database connection (Path is simpler now)
require_once(__DIR__ . '/includes/db.php');

try {
    echo "Starting migration...<br>";

    // 2. Point to your schema file inside the database folder
    $schemaFile = __DIR__ . '/database/schema.sql';

    if (!file_exists($schemaFile)) {
        die("Error: schema.sql not found at " . $schemaFile);
    }

    // 3. Read and execute the SQL content
    $sql = file_get_contents($schemaFile);
    $pdo->exec($sql);

    echo "<b>Success!</b> Your database tables have been created.<br>";
    echo "<a href='index.php'>Go to Homepage</a>";

} catch (PDOException $e) {
    echo "<b>Migration Failed:</b> " . $e->getMessage();
}