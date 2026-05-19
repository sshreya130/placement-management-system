<?php
require_once __DIR__ . '/../app/auth.php';
logout_user();
$base = base_path();
header('Location: ' . ($base !== '' ? $base : '') . '/index.php');
exit;

