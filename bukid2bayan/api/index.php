<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = ltrim($path, '/');
if ($path == '') $path = 'index.php';
$file = __DIR__ . '/../' . $path;
if (file_exists($file) && substr($file, -4) == '.php') {
    chdir(dirname($file));
    require $file;
} else {
    chdir(__DIR__ . '/..');
    require 'index.php';
}