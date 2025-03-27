<?php
date_default_timezone_set('Asia/Manila'); 
$host = 'localhost';
$dbname = 'vincenthinks_db'; 
$username = 'root';          
$password = '';              

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET time_zone = '+08:00'");

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
