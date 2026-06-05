<?php use App\Helpers\Formatter; ?>
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted small mb-1">Today's Sales</p>
                <h3 class="mb-0"><?= Formatter::money((float) ($todaySales['total'] ?? 0)) ?></h3>
                <small class="text-muted"><?= (int) ($todaySales['count'] ?? 0) ?> transactions</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted small mb-1">Today's Expenses</p>
                <h3 class="mb-0"><?= Formatter::money($todayExpenses ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted small mb-1">Est. Profit Today</p>
                <h3 class="mb-0 <?= ($profit ?? 0) >= 0 ? 'text-success' : 'text-danger' ?>"><?= Formatter::money($profit ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card border-0 shadow-sm">
            <div class="card-body">
                <p class="text-muted small mb-1">Products / Low Stock</p>
                <h3 class="mb-0"><?= (int) ($productCount ?? 0) ?> <span class="text-warning fs-6">(<?= count($lowStock ?? []) ?> low)</span></h3>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Recent Sales</strong></div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Receipt</th><th>Cashier</th><th>Total</th><th>Status</th><th>Time</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentSales ?? [] as $sale): ?>
                        <tr>
                            <td><a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $sale['id'] ?>"><?= e($sale['receipt_number']) ?></a></td>
                            <td><?= e($sale['cashier_name']) ?></td>
                            <td><?= Formatter::money((float) $sale['grand_total']) ?></td>
                            <td><span class="badge bg-<?= $sale['status'] === 'COMPLETED' ? 'success' : 'danger' ?>"><?= e($sale['status']) ?></span></td>
                            <td><?= Formatter::datetime($sale['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recentSales)): ?><tr><td colspan="5" class="text-muted text-center">No sales yet</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white"><strong>Quick Actions</strong></div>
            <div class="card-body d-grid gap-2">
                <?php if ($openShift ?? null): ?>
                    <a href="<?= e(appConfig('url')) ?>/pos" class="btn btn-primary btn-lg">Open POS</a>
                    <a href="<?= e(appConfig('url')) ?>/shifts/close" class="btn btn-outline-secondary">Close Shift</a>
                <?php else: ?>
                    <a href="<?= e(appConfig('url')) ?>/shifts/open" class="btn btn-primary btn-lg">Open Shift</a>
                <?php endif; ?>
                <?php if (auth()->role() === ROLE_ADMIN): ?>
                    <a href="<?= e(appConfig('url')) ?>/products/create" class="btn btn-outline-primary">Add Product</a>
                    <a href="<?= e(appConfig('url')) ?>/inventory/stock-in" class="btn btn-outline-primary">Stock In</a>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($lowStock)): ?>
        <div class="card border-0 shadow-sm border-warning">
            <div class="card-header bg-warning-subtle"><strong>Low Stock Alert</strong></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($lowStock as $p): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= e($p['name']) ?></span>
                        <span class="badge bg-warning text-dark"><?= (int) $p['stock_quantity'] ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>
