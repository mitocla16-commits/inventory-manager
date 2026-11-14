<?php
// Optional demo data: php seed.php
require __DIR__ . '/db.php';
$rows = [['KB-001','Mechanical keyboard','Peripherals',14,59.99],['MS-002','Wireless mouse','Peripherals',3,24.50],
['MN-003','27" monitor','Displays',8,189.00],['CB-004','USB-C cable 1m','Cables',42,6.99],['HD-005','1TB SSD','Storage',2,74.95],
['WC-006','HD webcam','Peripherals',11,39.00],['DK-007','Laptop stand','Accessories',19,29.99],['HB-008','USB hub 4-port','Accessories',27,15.49],
['MP-009','Mouse pad XL','Accessories',33,12.00],['HP-010','Headphones','Audio',6,49.99],['SP-011','Desktop speakers','Audio',4,34.50],['MC-012','USB microphone','Audio',9,64.00]];
$s = db()->prepare('INSERT OR IGNORE INTO items (sku,name,category,quantity,price) VALUES (?,?,?,?,?)');
foreach ($rows as $r) { $s->execute($r); }
echo "Seeded " . count($rows) . " items\n";
