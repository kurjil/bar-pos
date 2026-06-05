<?php use App\Helpers\Formatter; include __DIR__ . '/_filter.php'; $reportForm('by-product'); ?>
<h2 class="h4 mb-3">Sales by Product</h2>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
    <tbody><?php foreach ($rows as $r): ?><tr><td><?= e($r['product_name']) ?></td><td><?= (int) $r['qty'] ?></td><td><?= Formatter::money((float) $r['revenue']) ?></td></tr><?php endforeach; ?></tbody>
</table></div></div>
