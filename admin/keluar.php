<?php
/**
 * admin/keluar.php — Keluar dari halaman admin
 */

require_once __DIR__ . '/auth.php';

// Kosongkan dan hapus session
$_SESSION = [];
session_destroy();

header('Location: login.php');
exit;
