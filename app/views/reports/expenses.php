<?php use App\Helpers\Formatter; include __DIR__ . '/_filter.php'; $reportForm('expenses', 'expenses'); ?>
<h2 class="h4 mb-3">Expense Report</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <div class="text-muted small">Total Expenses</div>
                <div class="h3 mb-0"><?= Formatter::money($total) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0 small">
    <thead class="table-light">
        <tr>
            <th>Category</th>
            <th class="text-end">Count</th>
            <th class="text-end">Amount</th>
            <th class="text-end">% of Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows as $r): ?>
            <?php $pct = $total > 0 ? ((float)$r['total'] / $total * 100) : 0; ?>
            <tr>
                <td><?= e($r['category']) ?></td>
                <td class="text-end">-</td>
                <td class="text-end fw-bold"><?= Formatter::money((float) $r['total']) ?></td>
                <td class="text-end"><span class="badge bg-info"><?= number_format($pct, 1) ?>%</span></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light">
        <tr>
            <td><strong>TOTAL</strong></td>
            <td class="text-end">-</td>
            <td class="text-end fw-bold"><?= Formatter::money($total) ?></td>
            <td class="text-end"><strong>100%</strong></td>
        </tr>
    </tfoot>
</table></div></div>
