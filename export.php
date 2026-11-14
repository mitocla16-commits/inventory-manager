<?php
declare(strict_types=1);
require __DIR__ . '/db.php';

$q = trim((string)($_GET['q'] ?? ''));
$sql = 'SELECT sku, name, category, quantity, price FROM items';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE name LIKE :q OR sku LIKE :q OR category LIKE :q';
    $params[':q'] = '%' . $q . '%';
}
$stmt = db()->prepare($sql . ' ORDER BY name');
$stmt->execute($params);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="inventory-' . date('Y-m-d') . '.csv"');
$out = fopen('php://output', 'w');
fputcsv($out, ['SKU', 'Name', 'Category', 'Quantity', 'Price']);
foreach ($stmt as $row) {
    // Prevent spreadsheet formula injection (cells starting with = + - @).
    foreach (['sku', 'name', 'category'] as $col) {
        if (preg_match('/^[=+\-@]/', $row[$col])) {
            $row[$col] = "'" . $row[$col];
        }
    }
    fputcsv($out, $row);
}
fclose($out);
