<?php use App\Helpers\Formatter; include __DIR__ . '/_filter.php'; $reportForm('expenses'); ?>
<h2 class="h4 mb-3">Expense Report</h2>
<p><strong>Total:</strong> <?= Formatter::money($total) ?></p>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Category</th><th>Total</th></tr></thead>
    <tbody><?php foreach ($rows as $r): ?><tr><td><?= e($r['category']) ?></td><td><?= Formatter::money((float) $r['total']) ?></td></tr><?php endforeach; ?></tbody>
</table></div></div>
