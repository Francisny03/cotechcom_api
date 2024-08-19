<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include('env.php');
include('db_conf.php');


try {
    // SQL statements to create the tables
    $service = "CREATE TABLE services (
    id_service INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    descriptions TEXT,
    images VARCHAR(255),
    points TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $slider = "CREATE TABLE slider (
    id_slider INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    descriptions TEXT,
    images VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $realisation = "CREATE TABLE realisation (
    id_realisation INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    descriptions TEXT,
    images VARCHAR(255),
    dates date,
    imagess TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    $equipe = "CREATE TABLE equipe (
    id_equipe INT AUTO_INCREMENT PRIMARY KEY,
    descriptions VARCHAR(255) NOT NULL,
    images VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    

    // Execute the SQL statements
    $pdo->exec($service);
    $pdo->exec($slider);
    $pdo->exec($realisation);
    $pdo->exec($equipe);
    

    echo "Tables created successfully";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}

// Close the database connection
$conn = null;
?>

