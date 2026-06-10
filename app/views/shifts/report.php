<?php use App\Helpers\Formatter;
$disc = (float) ($shift['discrepancy'] ?? 0);
$discClass = $disc < 0 ? 'text-danger' : ($disc > 0 ? 'text-success' : '');
?>
<div class="mb-3">
    <h2 class="h4 d-inline">Shift Report #<?= (int) $shift['id'] ?></h2>
    <button type="button" id="printShiftReportBtn" class="btn btn-primary btn-sm float-end me-2" data-shift-id="<?= (int) $shift['id'] ?>">Print to Thermal</button>
    <a href="<?= e(appConfig('url')) ?>/shifts/print/<?= (int) $shift['id'] ?>" class="btn btn-outline-primary btn-sm float-end" target="_blank">Download Report</a>
</div>
<div class="card border-0 shadow-sm"><div class="card-body">
    <p><strong>Cashier:</strong> <?= e($shift['user_name']) ?></p>
    <p><strong>Opened:</strong> <?= Formatter::datetime($shift['opening_time']) ?></p>
    <p><strong>Closed:</strong> <?= Formatter::datetime($shift['closing_time'] ?? '') ?></p>
    <p><strong>Opening Float:</strong> <?= Formatter::money((float) $shift['opening_float']) ?></p>
    <p><strong>Cash Sales:</strong> <?= Formatter::money($cashSales) ?></p>
    <?php if (!empty($movements)): ?>
        <p><strong>Cash Movements:</strong></p>
        <ul class="mb-2">
            <?php foreach ($movements as $mvmt): ?>
                <li><?= ($mvmt['movement_type'] === 'FLOAT_IN' ? 'Float In' : 'Cash Drop') ?>: <?= Formatter::money($mvmt['amount']) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <p><strong>Expected Cash:</strong> <?= Formatter::money($expected) ?></p>
    <p><strong>Closing Float:</strong> <?= Formatter::money((float) ($shift['closing_float'] ?? 0)) ?></p>
    <p><strong>Discrepancy:</strong> <span class="<?= $discClass ?> fw-bold"><?= Formatter::money($disc) ?></span></p>
    <p><strong>Total Sales:</strong> <?= Formatter::money((float) $summary['total_sales']) ?></p>
</div></div>
<script>
window.APP_URL = <?= json_encode(appConfig('url')) ?>;
window.CSRF_TOKEN = <?= json_encode(session()->get('csrf_token')) ?>;
document.getElementById('printShiftReportBtn')?.addEventListener('click', function() {
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
