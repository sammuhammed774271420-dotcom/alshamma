<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit();
}
?>
