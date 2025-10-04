<?php
// logout.php
include 'db.php';
session_unset();
session_destroy();
echo "<script>alert('Logged out successfully!'); redirect('login.php');</script>";
?>
