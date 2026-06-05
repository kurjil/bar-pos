<?php use App\Helpers\Formatter; ?>
<h2 class="h4 mb-3 text-danger">Void Sale <?= e($sale['receipt_number']) ?></h2>
<p>This will void the sale and restore inventory. This action is logged.</p>
<form method="POST" action="<?= e(appConfig('url')) ?>/sales/<?= (int) $sale['id'] ?>/void" onsubmit="return confirm('Void this sale?')">
    <?= csrfField() ?>
    <button class="btn btn-danger">Confirm Void</button>
    <a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $sale['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
</form>
