<?php
declare(strict_types=1);

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

function e(?string $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
}

function require_admin(): void
{
    if (empty($_SESSION['admin'])) {
        header('Location: login.php');
        exit;
    }
}
