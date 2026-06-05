<?php use App\Helpers\Formatter; include __DIR__ . '/_filter.php'; $reportForm('by-category'); ?>
<h2 class="h4 mb-3">Sales by Category</h2>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Category</th><th>Revenue</th></tr></thead>
    <tbody><?php foreach ($rows as $r): ?><tr><td><?= e($r['category_name']) ?></td><td><?= Formatter::money((float) $r['revenue']) ?></td></tr><?php endforeach; ?></tbody>
</table></div></div>
