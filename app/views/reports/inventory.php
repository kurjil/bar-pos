<?php use App\Helpers\Formatter; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Inventory Report</h2>
    <a href="<?= e(appConfig('url')) ?>/reports/export/inventory" class="btn btn-success btn-sm">Export Excel</a>
</div>
<?php if (!empty($lowStock)): ?><div class="alert alert-warning"><?= count($lowStock) ?> product(s) below minimum stock.</div><?php endif; ?>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Product</th><th>Category</th><th>Stock</th><th>Min</th><th>Status</th></tr></thead>
    <tbody><?php foreach ($products as $p): ?><tr class="<?= (int)$p['stock_quantity'] <= (int)$p['minimum_stock'] ? 'table-warning' : '' ?>">
        <td><?= e($p['name']) ?></td><td><?= e($p['category_name']) ?></td><td><?= (int) $p['stock_quantity'] ?></td><td><?= (int) $p['minimum_stock'] ?></td>
        <td><?= (int)$p['stock_quantity'] <= (int)$p['minimum_stock'] ? 'Low' : 'OK' ?></td></tr><?php endforeach; ?></tbody>
</table></div></div>
