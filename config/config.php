<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

define('BASE_URL', rtrim($_ENV['BASE_URL'] ?? 'http://localhost', '/'));

function url($path = '') {
    $path = ltrim($path, '/');
    if (empty($path)) {
        return BASE_URL;
    }
    return BASE_URL . '/' . $path;
}

function asset($path = '') {
    $path = ltrim($path, '/');
    if (empty($path)) {
        return BASE_URL;
    }
    return BASE_URL . '/' . $path;
}