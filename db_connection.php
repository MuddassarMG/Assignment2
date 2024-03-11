<?php

// host
$db_host = 'localhost'; 
// Database name
$db_name = 'ecommercesite';
$db_user = 'root'; 
$db_password = '';

// connecting to the database
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
    // Set PDO to throw exceptions on error
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    //if the connection failed, will show the error
    die("Connection failed: " . $e->getMessage());
}

?>
