<?php use App\Helpers\Formatter; include __DIR__ . '/_filter.php'; $reportForm('daily-sales'); ?>
<h2 class="h4 mb-3">Daily Sales</h2>
<div class="card border-0 shadow-sm"><div class="card-body">
    <h3>Today: <?= Formatter::money((float) $today['total']) ?></h3>
    <p class="text-muted mb-0"><?= (int) $today['count'] ?> transactions</p>
</div></div>
