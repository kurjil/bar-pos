<?php use App\Helpers\Formatter; include __DIR__ . '/_filter.php'; $reportForm('end-of-day', 'end-of-day'); ?>
<h2 class="h4 mb-3">End of Day Report</h2>
<p class="text-muted">Daily sales totals combined with expenses. Net profit = sales − expenses.</p>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <div class="text-muted small">Total Sales</div>
                <div class="h4 mb-0"><?= Formatter::money((float) $totals['sales']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <div class="text-muted small">Total Expenses</div>
                <div class="h4 mb-0 text-danger"><?= Formatter::money((float) $totals['expenses']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <div class="text-muted small">Net Profit</div>
                <div class="h4 mb-0 <?= $totals['profit'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= Formatter::money((float) $totals['profit']) ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <div class="text-muted small">Transactions</div>
                <div class="h4 mb-0"><?= (int) $totals['transactions'] ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th class="text-end">Transactions</th>
                    <th class="text-end">Total Sales</th>
                    <th class="text-end">Total Expenses</th>
                    <th class="text-end">Net Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($days)): ?>
                <tr><td colspan="5" class="text-center text-muted">No data for selected period</td></tr>
                <?php endif; ?>
                <?php foreach ($days as $day): ?>
                <tr>
                    <td><?= date('D, M j, Y', strtotime($day['date'])) ?></td>
                    <td class="text-end"><?= (int) $day['transaction_count'] ?></td>
                    <td class="text-end"><?= Formatter::money((float) $day['total_sales']) ?></td>
                    <td class="text-end text-danger"><?= Formatter::money((float) $day['total_expenses']) ?></td>
                    <td class="text-end fw-bold <?= $day['net_profit'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= Formatter::money((float) $day['net_profit']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
