<?php use App\Helpers\Formatter; ?>
<h2 class="h4 mb-3">Sale <?= e($sale['receipt_number']) ?></h2>
<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm"><div class="card-body">
            <p><strong>Cashier:</strong> <?= e($sale['cashier_name']) ?></p>
            <p><strong>Date:</strong> <?= Formatter::datetime($sale['created_at']) ?></p>
            <p><strong>Payment:</strong> <?= e($sale['payment_method']) ?></p>
            <p><strong>Status:</strong> <?= e($sale['status']) ?></p>
            <p><strong>Total:</strong> <?= Formatter::money((float) $sale['grand_total']) ?></p>
            <?php if ($sale['status'] === 'COMPLETED' && auth()->role() === ROLE_ADMIN): ?>
                <a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $sale['id'] ?>/void" class="btn btn-danger">Void Sale</a>
            <?php endif; ?>
            <?php if ($sale['status'] === 'COMPLETED'): ?>
            <button type="button" id="printReceiptBtn" class="btn btn-primary" data-sale-id="<?= (int) $sale['id'] ?>">Print Receipt</button>
            <?php endif; ?>
            <a href="<?= e(appConfig('url')) ?>/pos/receipt/<?= (int) $sale['id'] ?>" class="btn btn-outline-secondary" target="_blank">Browser Receipt</a>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0">
            <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $i): ?>
                <tr><td><?= e($i['product_name']) ?></td><td><?= (int) $i['quantity'] ?></td><td><?= Formatter::money((float) $i['unit_price']) ?></td><td><?= Formatter::money((float) $i['line_total']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div></div>
    </div>
</div>
<script>
window.APP_URL = <?= json_encode(appConfig('url')) ?>;
window.CSRF_TOKEN = <?= json_encode(session()->get('csrf_token')) ?>;
document.getElementById('printReceiptBtn')?.addEventListener('click', function() {
    var btn = this;
    btn.disabled = true;
    fetch(window.APP_URL + '/api/sales/' + btn.dataset.saleId + '/print', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ csrf_token: window.CSRF_TOKEN })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { alert(data.success ? data.message : data.message); btn.disabled = false; })
    .catch(function() { alert('Network error'); btn.disabled = false; });
});
</script>
