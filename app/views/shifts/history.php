<?php use App\Helpers\Formatter; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4 mb-0">Shift History</h2>
    <a href="<?= e(appConfig('url')) ?>/shifts/open" class="btn btn-outline-primary btn-sm">Open Shift</a>
</div>

<?php if ($isAdmin): ?>
<form method="GET" class="row g-2 mb-3 align-items-end">
    <div class="col-auto">
        <label class="form-label small mb-0">Cashier</label>
        <select name="user_id" class="form-select form-select-sm">
            <option value="">All cashiers</option>
            <?php foreach ($users as $u): ?>
            <option value="<?= (int) $u['id'] ?>" <?= $filterUserId == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-primary btn-sm">Filter</button></div>
</form>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Cashier</th>
                    <th class="text-end">Opening</th>
                    <th class="text-end">Closing</th>
                    <th class="text-end">Discrepancy</th>
                    <th class="text-end">Sales</th>
                    <th class="text-end">Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($shifts)): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">No closed shifts found</td></tr>
                <?php endif; ?>
                <?php foreach ($shifts as $shift): ?>
                <?php
                    $disc = (float) ($shift['discrepancy'] ?? 0);
                    $discClass = $disc < 0 ? 'text-danger bg-danger-subtle' : 'text-success';
                    $avg = (int) $shift['transaction_count'] > 0
                        ? (float) $shift['total_sales'] / (int) $shift['transaction_count']
                        : 0;
                ?>
                <tr>
                    <td><?= date('D, M j, Y', strtotime($shift['closing_time'] ?? $shift['opening_time'])) ?></td>
                    <td><?= date('H:i', strtotime($shift['opening_time'])) ?> - <?= date('H:i', strtotime($shift['closing_time'] ?? '')) ?></td>
                    <td><?= e($shift['user_name']) ?></td>
                    <td class="text-end"><?= Formatter::money((float) $shift['opening_float']) ?></td>
                    <td class="text-end"><?= Formatter::money((float) ($shift['closing_float'] ?? 0)) ?></td>
                    <td class="text-end fw-bold <?= $discClass ?>"><?= Formatter::money($disc) ?></td>
                    <td class="text-end"><?= (int) $shift['transaction_count'] ?></td>
                    <td class="text-end"><?= Formatter::money((float) $shift['total_sales']) ?></td>
                    <td class="text-nowrap">
                        <a href="<?= e(appConfig('url')) ?>/shifts/<?= (int) $shift['id'] ?>/detail" class="btn btn-sm btn-outline-primary">Details</a>
                        <button type="button" class="btn btn-sm btn-outline-secondary print-shift-btn" data-shift-id="<?= (int) $shift['id'] ?>">Print</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav class="mt-3">
    <ul class="pagination pagination-sm justify-content-center">
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="page-item <?= $p === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?><?= $filterUserId ? '&user_id=' . (int) $filterUserId : '' ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>

<script>
window.APP_URL = <?= json_encode(appConfig('url')) ?>;
window.CSRF_TOKEN = <?= json_encode(session()->get('csrf_token')) ?>;
document.querySelectorAll('.print-shift-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var shiftId = btn.dataset.shiftId;
        btn.disabled = true;
        fetch(window.APP_URL + '/api/shifts/' + shiftId + '/print-report', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ csrf_token: window.CSRF_TOKEN })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            alert(data.success ? data.message : (data.message || 'Print failed'));
            btn.disabled = false;
        })
        .catch(function() { alert('Network error'); btn.disabled = false; });
    });
});
</script>
