<?php use App\Helpers\Formatter; ?>
<h2 class="h4 mb-3">Inventory Report</h2>
<?php if (!empty($lowStock)): ?><div class="alert alert-warning"><?= count($lowStock) ?> product(s) below minimum stock.</div><?php endif; ?>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Product</th><th>Category</th><th>Stock</th><th>Min</th><th>Status</th></tr></thead>
    <tbody><?php foreach ($products as $p): ?><tr class="<?= (int)$p['stock_quantity'] <= (int)$p['minimum_stock'] ? 'table-warning' : '' ?>">
        <td><?= e($p['name']) ?></td><td><?= e($p['category_name']) ?></td><td><?= (int) $p['stock_quantity'] ?></td><td><?= (int) $p['minimum_stock'] ?></td>
        <td><?= (int)$p['stock_quantity'] <= (int)$p['minimum_stock'] ? 'Low' : 'OK' ?></td></tr><?php endforeach; ?></tbody>
</table></div></div>
