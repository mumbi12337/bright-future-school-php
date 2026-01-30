<?php
require_once dirname(__DIR__) . '/includes/db.php';

echo "Starting database migration...\n";

try {
    // Read the SQL schema file
    $sql = file_get_contents(dirname(__FILE__) . '/schema.sql');
    
    if ($sql === false) {
        throw new Exception("Could not read schema.sql file");
    }
    
    // Execute the SQL
    $pdo->exec($sql);
    
    echo "Database schema created successfully!\n";
    
    // Insert default admin user if users table is empty
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'ADMIN'");
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount == 0) {
            // Insert default admin user with hashed password
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $insertAdmin = "INSERT INTO users (email, password, role) VALUES ('admin@school.edu', ?, 'ADMIN')";
            $stmt = $pdo->prepare($insertAdmin);
            $stmt->execute([$defaultPassword]);
            
            echo "Default admin user created:\n";
            echo "  Email: admin@school.edu\n";
            echo "  Password: admin123\n";
        }
    } catch (Exception $e) {
        echo "Note: Could not insert default admin user (table may not exist yet): " . $e->getMessage() . "\n";
    }
    
    echo "Migration completed successfully!\n";
} catch (Exception $e) {
    echo "Error during migration: " . $e->getMessage() . "\n";
}
?>