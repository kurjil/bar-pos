<?php use App\Helpers\Formatter;
$disc = (float) ($shift['discrepancy'] ?? 0);
$discClass = $disc < 0 ? 'text-danger' : 'text-success';
?>
<h3 class="h4 mb-3">Shift #<?= (int) $shift['id'] ?></h3>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><strong>Shift Summary</strong></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Cashier:</strong> <?= e($shift['user_name']) ?></p>
                <p><strong>Opened:</strong> <?= Formatter::datetime($shift['opening_time']) ?></p>
                <p><strong>Closed:</strong> <?= Formatter::datetime($shift['closing_time'] ?? '') ?></p>
                <p><strong>Duration:</strong> <?= $totals['duration_hours'] ?> hours</p>
                <p><strong>Status:</strong> <?= e($shift['status']) ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Opening Float:</strong> <?= Formatter::money((float) $shift['opening_float']) ?></p>
                <p><strong>Closing Float:</strong> <?= Formatter::money((float) ($shift['closing_float'] ?? 0)) ?></p>
                <p><strong>Expected Cash:</strong> <?= Formatter::money($expected) ?></p>
                <p><strong>Discrepancy:</strong> <span class="<?= $discClass ?> fw-bold"><?= Formatter::money($disc) ?></span></p>
                <p><strong>Cash Sales:</strong> <?= Formatter::money($cashSales) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><strong>Sales During Shift</strong></div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Receipt #</th>
                    <th>Time</th>
                    <th class="text-end">Items</th>
                    <th class="text-end">Total</th>
                    <th>Payment</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                <tr><td colspan="6" class="text-center text-muted">No sales</td></tr>
                <?php endif; ?>
                <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $sale['id'] ?>"><?= e($sale['receipt_number']) ?></a></td>
                    <td><?= date('H:i:s', strtotime($sale['created_at'])) ?></td>
                    <td class="text-end"><?= (int) $sale['items_count'] ?></td>
                    <td class="text-end"><?= Formatter::money((float) $sale['grand_total']) ?></td>
                    <td><?= e($sale['payment_method']) ?></td>
                    <td><a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $sale['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($movements)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><strong>Cash Movements</strong></div>
    <div class="table-responsive">
        <table class="table table-sm mb-0">
            <thead><tr><th>Type</th><th class="text-end">Amount</th><th>Notes</th><th>Time</th></tr></thead>
            <tbody>
                <?php foreach ($movements as $mvmt): ?>
                <tr>
                    <td><?= e($mvmt['movement_type']) ?></td>
                    <td class="text-end"><?= Formatter::money((float) $mvmt['amount']) ?></td>
                    <td><?= e($mvmt['notes'] ?? '') ?></td>
                    <td><?= Formatter::datetime($mvmt['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><strong>Totals</strong></div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3"><strong>Transactions:</strong> <?= $totals['sales_count'] ?></div>
            <div class="col-md-3"><strong>Total Sales:</strong> <?= Formatter::money($totals['total_sales']) ?></div>
            <div class="col-md-3"><strong>Avg Transaction:</strong> <?= Formatter::money($totals['avg_transaction']) ?></div>
            <div class="col-md-3"><strong>Float In:</strong> <?= Formatter::money($totals['float_in_total']) ?> / <strong>Drop:</strong> <?= Formatter::money($totals['cash_drop_total']) ?></div>
        </div>
    </div>
</div>

<div class="d-flex gap-2">
    <button type="button" id="printReportBtn" class="btn btn-primary" data-shift-id="<?= (int) $shift['id'] ?>">Print Report</button>
    <a href="<?= e(appConfig('url')) ?>/shifts/print/<?= (int) $shift['id'] ?>" class="btn btn-outline-secondary" target="_blank">Download Text Report</a>
    <a href="<?= e(appConfig('url')) ?>/shifts/history" class="btn btn-secondary">Back to History</a>
</div>

<script>
window.APP_URL = <?= json_encode(appConfig('url')) ?>;
window.CSRF_TOKEN = <?= json_encode(session()->get('csrf_token')) ?>;
document.getElementById('printReportBtn').addEventListener('click', function() {
    var btn = this;
    btn.disabled = true;
    fetch(window.APP_URL + '/api/shifts/' + btn.dataset.shiftId + '/print-report', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ csrf_token: window.CSRF_TOKEN })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) { alert(data.success ? data.message : data.message); btn.disabled = false; })
    .catch(function() { alert('Network error'); btn.disabled = false; });
});
</script>
