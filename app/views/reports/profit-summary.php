<?php use App\Helpers\Formatter; include __DIR__ . '/_filter.php'; $reportForm('profit-summary'); ?>
<h2 class="h4 mb-3">Profit Summary</h2>
<div class="row g-3">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-muted mb-1">Sales</p><h3><?= Formatter::money($salesTotal) ?></h3></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-muted mb-1">Expenses</p><h3><?= Formatter::money($expensesTotal) ?></h3></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><p class="text-muted mb-1">Profit</p><h3 class="<?= $profit >= 0 ? 'text-success' : 'text-danger' ?>"><?= Formatter::money($profit) ?></h3></div></div></div>
</div>
