<?php use App\Helpers\Formatter; ?>
<div class="d-flex justify-content-between mb-3">
    <h2 class="h4 mb-0">Sales</h2>
    <span class="text-muted"><?= (int) $total ?> total</span>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <form method="GET" action="<?= e(appConfig('url')) ?>/sales" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-0">From</label>
                <input type="date" name="from" class="form-control form-control-sm" value="<?= e($filters['from']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">To</label>
                <input type="date" name="to" class="form-control form-control-sm" value="<?= e($filters['to']) ?>">
            </div>
            <?php if ($isAdmin): ?>
            <div class="col-md-2">
                <label class="form-label small mb-0">Cashier</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= (int) $u['id'] ?>" <?= $filters['user_id'] == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-2">
                <label class="form-label small mb-0">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="COMPLETED" <?= $filters['status'] === 'COMPLETED' ? 'selected' : '' ?>>Completed</option>
                    <option value="VOIDED" <?= $filters['status'] === 'VOIDED' ? 'selected' : '' ?>>Voided</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">Payment</label>
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="CASH" <?= $filters['payment_method'] === 'CASH' ? 'selected' : '' ?>>Cash</option>
                    <option value="MOBILE_MONEY" <?= $filters['payment_method'] === 'MOBILE_MONEY' ? 'selected' : '' ?>>Mobile Money</option>
                    <option value="CARD" <?= $filters['payment_method'] === 'CARD' ? 'selected' : '' ?>>Card</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="<?= e(appConfig('url')) ?>/sales" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Receipt</th>
                    <th>Cashier</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sales as $s): ?>
                <tr>
                    <td><a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $s['id'] ?>"><?= e($s['receipt_number']) ?></a></td>
                    <td><?= e($s['cashier_name']) ?></td>
                    <td><?= Formatter::money((float) $s['grand_total']) ?></td>
                    <td><?= e($s['payment_method']) ?></td>
                    <td><span class="badge bg-<?= $s['status'] === 'COMPLETED' ? 'success' : 'danger' ?>"><?= e($s['status']) ?></span></td>
                    <td><?= Formatter::datetime($s['created_at']) ?></td>
                    <td class="text-nowrap">
                        <a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                        <?php if ($s['status'] === 'COMPLETED'): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary print-receipt-btn" data-sale-id="<?= (int) $s['id'] ?>">Print</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($sales)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No sales found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<script>
window.APP_URL = <?= json_encode(appConfig('url')) ?>;
window.CSRF_TOKEN = <?= json_encode(session()->get('csrf_token')) ?>;
document.querySelectorAll('.print-receipt-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var saleId = btn.dataset.saleId;
        btn.disabled = true;
        btn.textContent = 'Printing...';
        fetch(window.APP_URL + '/api/sales/' + saleId + '/print', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ csrf_token: window.CSRF_TOKEN })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            alert(data.success ? data.message : (data.message || 'Print failed'));
            btn.disabled = false;
            btn.textContent = 'Print';
        })
        .catch(function() {
            alert('Network error');
            btn.disabled = false;
            btn.textContent = 'Print';
        });
    });
});
</script>
