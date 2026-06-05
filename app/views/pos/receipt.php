<?php use App\Helpers\Formatter; ?>
<div class="receipt-print p-4" style="max-width:400px;margin:0 auto;font-family:monospace">
    <div class="text-center mb-3">
        <h4><?= e($settings['business_name'] ?? 'Bar POS') ?></h4>
        <small><?= e($settings['business_address'] ?? '') ?></small><br>
        <small><?= e($settings['business_phone'] ?? '') ?></small>
    </div>
    <hr>
    <p>Receipt: <?= e($sale['receipt_number']) ?><br>Date: <?= Formatter::datetime($sale['created_at']) ?><br>Cashier: <?= e($sale['cashier_name']) ?></p>
    <hr>
    <?php foreach ($items as $i): ?>
        <div class="d-flex justify-content-between"><span><?= e($i['product_name']) ?> x<?= (int) $i['quantity'] ?></span><span><?= Formatter::money((float) $i['line_total']) ?></span></div>
    <?php endforeach; ?>
    <hr>
    <div class="d-flex justify-content-between"><span>Subtotal</span><span><?= Formatter::money((float) $sale['subtotal']) ?></span></div>
    <?php if ((float)$sale['discount_value'] > 0): ?><div class="d-flex justify-content-between"><span>Discount</span><span>-<?= Formatter::money((float) $sale['discount_value']) ?></span></div><?php endif; ?>
    <div class="d-flex justify-content-between fw-bold"><span>TOTAL</span><span><?= Formatter::money((float) $sale['grand_total']) ?></span></div>
    <p class="mt-2">Payment: <?= e($sale['payment_method']) ?></p>
    <div class="text-center mt-4"><button onclick="window.print()" class="btn btn-primary">Print</button></div>
</div>
