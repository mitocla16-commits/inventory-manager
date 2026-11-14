<?php
declare(strict_types=1);
require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
    http_response_code(400);
    exit('Bad request');
}
$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if ($id) {
    db()->prepare('DELETE FROM items WHERE id = :id')->execute([':id' => $id]);
    $_SESSION['flash'] = 'Item deleted.';
}
header('Location: index.php');
