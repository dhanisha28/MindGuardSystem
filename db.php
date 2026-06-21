<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'mindguard_db';

$con = mysqli_connect($host, $user, $pass, $db);
if (!$con) die('Database connection failed');
mysqli_set_charset($con, 'utf8mb4');
date_default_timezone_set('Asia/Kuala_Lumpur');
session_start();
?>
