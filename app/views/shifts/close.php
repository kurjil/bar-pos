<?php use App\Helpers\Formatter; ?>
<h2 class="h4 mb-3">Close Shift</h2>
<div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <p><strong>Opening Float:</strong> <?= Formatter::money((float) $shift['opening_float']) ?></p>
    <p><strong>Cash Sales:</strong> <?= Formatter::money($cashSales) ?></p>
    <p><strong>Expected Cash:</strong> <?= Formatter::money($expected) ?></p>
    <p><strong>Total Sales (all methods):</strong> <?= Formatter::money((float) $summary['total_sales']) ?> (<?= (int) $summary['transaction_count'] ?> txns)</p>
</div></div>
<form method="POST" action="<?= e(appConfig('url')) ?>/shifts/close">
    <?= csrfField() ?>
    <div class="mb-3"><label class="form-label">Actual Cash Count</label>
        <input type="number" step="0.01" name="closing_float" class="form-control form-control-lg" required min="0"></div>
    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
    <button class="btn btn-primary btn-lg">Close Shift</button>
</form>
