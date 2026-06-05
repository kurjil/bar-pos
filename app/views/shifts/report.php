<?php use App\Helpers\Formatter;
$disc = (float) ($shift['discrepancy'] ?? 0);
$discClass = $disc < 0 ? 'text-danger' : ($disc > 0 ? 'text-success' : '');
?>
<h2 class="h4 mb-3">Shift Report #<?= (int) $shift['id'] ?></h2>
<div class="card border-0 shadow-sm"><div class="card-body">
    <p><strong>Cashier:</strong> <?= e($shift['user_name']) ?></p>
    <p><strong>Opened:</strong> <?= Formatter::datetime($shift['opening_time']) ?></p>
    <p><strong>Closed:</strong> <?= Formatter::datetime($shift['closing_time'] ?? '') ?></p>
    <p><strong>Opening Float:</strong> <?= Formatter::money((float) $shift['opening_float']) ?></p>
    <p><strong>Cash Sales:</strong> <?= Formatter::money($cashSales) ?></p>
    <p><strong>Expected Cash:</strong> <?= Formatter::money($expected) ?></p>
    <p><strong>Closing Float:</strong> <?= Formatter::money((float) ($shift['closing_float'] ?? 0)) ?></p>
    <p><strong>Discrepancy:</strong> <span class="<?= $discClass ?> fw-bold"><?= Formatter::money($disc) ?></span></p>
    <p><strong>Total Sales:</strong> <?= Formatter::money((float) $summary['total_sales']) ?></p>
</div></div>
