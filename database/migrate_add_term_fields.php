<?php
/**
 * Migration: Add current_term and academic_year columns to students table
 * Run once at: http://localhost/bright-future-school-php/database/migrate_add_term_fields.php
 */
require_once dirname(__DIR__) . '/includes/db.php';

// Only allow if admin is logged in OR if running from CLI
if (php_sapi_name() !== 'cli') {
    require_once dirname(__DIR__) . '/includes/Auth.php';
    $auth = new Auth();
    if (!$auth->isLoggedIn() || !$auth->isAdmin()) {
        die("<h2>Unauthorized. Please log in as admin first.</h2>");
    }
}

global $pdo;

$migrations = [
    "Add current_term column" => "ALTER TABLE students ADD COLUMN IF NOT EXISTS current_term INTEGER DEFAULT 1",
    "Add academic_year column" => "ALTER TABLE students ADD COLUMN IF NOT EXISTS academic_year INTEGER DEFAULT " . date('Y'),
    "Set academic_year for existing students" => "UPDATE students SET academic_year = " . date('Y') . " WHERE academic_year IS NULL",
    "Set current_term for existing students"  => "UPDATE students SET current_term = 1 WHERE current_term IS NULL",
];

echo "<h2>Running Migrations...</h2><ul>";
$errors = [];
foreach ($migrations as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "<li style='color:green'>✅ {$name}: OK</li>";
    } catch (PDOException $e) {
        $msg = $e->getMessage();
        // If column already exists it's fine
        if (strpos($msg, 'already exists') !== false) {
            echo "<li style='color:orange'>⚠️ {$name}: Already exists (skipped)</li>";
        } else {
            echo "<li style='color:red'>❌ {$name}: {$msg}</li>";
            $errors[] = $name;
        }
    }
}
echo "</ul>";

if (empty($errors)) {
    echo "<p style='color:green'><strong>Migration completed successfully!</strong></p>";
    echo "<p>You can delete this file or keep it (it is safe to re-run).</p>";
} else {
    echo "<p style='color:red'><strong>Migration completed with errors. Check above.</strong></p>";
}
?>
