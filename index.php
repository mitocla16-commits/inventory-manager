<?php
declare(strict_types=1);
require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';

$q = trim((string)($_GET['q'] ?? ''));
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));

$where = '';
$params = [];
if ($q !== '') {
    $where = ' WHERE name LIKE :q OR sku LIKE :q OR category LIKE :q';
    $params[':q'] = '%' . $q . '%';
}

$count = db()->prepare("SELECT COUNT(*) FROM items$where");
$count->execute($params);
$total = (int)$count->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);

$stmt = db()->prepare("SELECT * FROM items$where ORDER BY name ASC LIMIT $perPage OFFSET " . (($page - 1) * $perPage));
$stmt->execute($params);
$items = $stmt->fetchAll();

$stats = db()->query('SELECT COUNT(*) n, COALESCE(SUM(quantity * price),0) v, COALESCE(SUM(quantity <= 5),0) low FROM items')->fetch();
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$link = fn(int $p) => '?' . http_build_query(array_filter(['q' => $q, 'page' => $p > 1 ? $p : null]));
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inventory manager</title><link rel="stylesheet" href="assets/style.css"></head>
<body><main class="card wide">
<div class="topbar"><h1>Inventory</h1><div><a class="btn" href="edit.php">+ Add item</a> <a class="btn" href="export.php?q=<?= e(urlencode($q)) ?>">Export CSV</a></div></div>
<?php if ($flash): ?><div class="alert ok"><?= e($flash) ?></div><?php endif; ?>
<p class="muted"><?= (int)$stats['n'] ?> items · stock value £<?= number_format((float)$stats['v'], 2) ?> · <?= (int)$stats['low'] ?> low stock (5 or fewer)</p>
<form method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search name, SKU or category..."></form>
<?php if (!$items): ?><p class="muted">No items found.</p><?php else: ?>
<table>
<tr><th>SKU</th><th>Name</th><th>Category</th><th>Qty</th><th>Price</th><th></th></tr>
<?php foreach ($items as $i): ?>
<tr>
    <td><?= e($i['sku']) ?></td><td><?= e($i['name']) ?></td><td><?= e($i['category']) ?></td>
    <td><?= (int)$i['quantity'] ?> <?php if ($i['quantity'] <= 5): ?><span class="badge cancelled">low</span><?php endif; ?></td>
    <td>£<?= number_format((float)$i['price'], 2) ?></td>
    <td>
        <a class="btn small" style="margin:0;padding:6px 10px" href="edit.php?id=<?= (int)$i['id'] ?>">Edit</a>
        <form method="post" action="delete.php" class="inline" onsubmit="return confirm('Delete this item?')"><?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$i['id'] ?>"><button class="small danger">Delete</button></form>
    </td>
</tr>
<?php endforeach; ?>
</table>
<p class="small">Page <?= $page ?> of <?= $pages ?>
    <?php if ($page > 1): ?> · <a href="<?= e($link($page - 1)) ?>">Previous</a><?php endif; ?>
    <?php if ($page < $pages): ?> · <a href="<?= e($link($page + 1)) ?>">Next</a><?php endif; ?></p>
<?php endif; ?>
</main></body></html>
