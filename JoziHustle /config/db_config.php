<?php
// db_config.php
$servername = "localhost";
$username = "jozihum2y7r5_jozi_hustle_db_user";
$password = "_3#A,F1M&ny$~UAF";
$dbname = "jozihum2y7r5_jozi_hustle_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo = $conn;
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
