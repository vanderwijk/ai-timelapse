<?php
try {
    $db = new PDO('sqlite:database.db');

    // Create settings table
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY,
        ai_prompt TEXT,
        locations TEXT
    )");

    // Create uploads table
    $db->exec("CREATE TABLE IF NOT EXISTS uploads (
        id INTEGER PRIMARY KEY,
        filename TEXT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        score INTEGER,
        explanation TEXT
    )");

    echo "Database and tables created successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>