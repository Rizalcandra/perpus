<?php
/**
 * index.php - Router utama
 * File ini mendefinisikan constant dan meng-include header + home + footer
 */
define('APP_NAME', 'Perpustakaan Desa');
require_once __DIR__ . '/config.php';
$page_title = 'Beranda';
require_once __DIR__ . '/partials/header.php';
require_once __DIR__ . '/home.php';
require_once __DIR__ . '/partials/footer.php';
