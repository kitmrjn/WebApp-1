<?php
$host = 'localhost';
$dbname = 'vincenthinks_db'; // Change if needed
$username = 'root';          // Change if needed
$password = '';              // Change if needed

try {
    // Create a PDO instance
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Set PDO to throw exceptions on errors
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optional debug line: 
    // echo "Connected successfully!";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
