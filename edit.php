<?php
declare(strict_types=1);
require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';

$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT) ?: null;
$item = ['sku' => '', 'name' => '', 'category' => '', 'quantity' => '0', 'price' => '0.00'];
if ($id) {
    $s = db()->prepare('SELECT * FROM items WHERE id = :id');
    $s->execute([':id' => $id]);
    $item = $s->fetch();
    if (!$item) {
        http_response_code(404);
        exit('Item not found');
    }
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item = [
        'sku' => strtoupper(trim((string)($_POST['sku'] ?? ''))),
        'name' => trim((string)($_POST['name'] ?? '')),
        'category' => trim((string)($_POST['category'] ?? '')),
        'quantity' => trim((string)($_POST['quantity'] ?? '')),
        'price' => trim((string)($_POST['price'] ?? '')),
    ];
    if (!csrf_check()) {
        $errors[] = 'Session expired. Reload and try again.';
    }
    if (!preg_match('/^[A-Z0-9-]{2,40}$/', $item['sku'])) {
        $errors[] = 'SKU must be 2-40 characters: letters, numbers or dashes.';
    }
    if ($item['name'] === '' || mb_strlen($item['name']) > 120) {
        $errors[] = 'Name is required (max 120 characters).';
    }
    if ($item['category'] === '' || mb_strlen($item['category']) > 60) {
        $errors[] = 'Category is required (max 60 characters).';
    }
    if (filter_var($item['quantity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
        $errors[] = 'Quantity must be a whole number, 0 or more.';
    }
    if (!is_numeric($item['price']) || (float)$item['price'] < 0) {
        $errors[] = 'Price must be a number, 0 or more.';
    }

    if (!$errors) {
        try {
            $p = [':sku' => $item['sku'], ':name' => $item['name'], ':cat' => $item['category'],
                  ':qty' => (int)$item['quantity'], ':price' => round((float)$item['price'], 2)];
            if ($id) {
                $p[':id'] = $id;
                db()->prepare('UPDATE items SET sku=:sku, name=:name, category=:cat, quantity=:qty, price=:price, updated_at=CURRENT_TIMESTAMP WHERE id=:id')->execute($p);
            } else {
                db()->prepare('INSERT INTO items (sku, name, category, quantity, price) VALUES (:sku,:name,:cat,:qty,:price)')->execute($p);
            }
            $_SESSION['flash'] = $id ? 'Item updated.' : 'Item added.';
            header('Location: index.php');
            exit;
        } catch (PDOException $ex) {
            if (in_array($ex->getCode(), ['23000', 23000, '23505'], true)) {
                $errors[] = 'That SKU already exists.';
            } else {
                error_log($ex->getMessage());
                $errors[] = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $id ? 'Edit' : 'Add' ?> item</title><link rel="stylesheet" href="assets/style.css"></head>
<body><main class="card">
<h1><?= $id ? 'Edit item' : 'Add item' ?></h1>
<?php if ($errors): ?><div class="alert err"><ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post"><?= csrf_field() ?>
<label>SKU <input name="sku" value="<?= e($item['sku']) ?>" required maxlength="40"></label>
<label>Name <input name="name" value="<?= e($item['name']) ?>" required maxlength="120"></label>
<label>Category <input name="category" value="<?= e($item['category']) ?>" required maxlength="60"></label>
<div class="row">
<label>Quantity <input type="number" name="quantity" min="0" step="1" value="<?= e((string)$item['quantity']) ?>" required></label>
<label>Price (£) <input type="number" name="price" min="0" step="0.01" value="<?= e((string)$item['price']) ?>" required></label>
</div>
<button type="submit">Save</button> <a href="index.php">Cancel</a>
</form></main></body></html>
