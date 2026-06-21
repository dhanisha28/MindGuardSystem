<?php
require_once 'db.php';
if (!isset($_SESSION['counsellor_id'])) {
    header('Location: login.php');
    exit;
}
?>