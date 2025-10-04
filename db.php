<?php
// db.php
// Start session with consistent configuration
ini_set('session.cookie_lifetime', 86400); // 24-hour session
ini_set('session.gc_maxlifetime', 86400);
session_start();

$host = 'localhost';
$dbname = 'dbr7whmx3olkvg';
$username = 'uczrllawgyzfy';
$password = 'tmq3v2ylpxpl';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("<script>alert('Connection failed: " . addslashes($e->getMessage()) . "');</script>");
}
?>
