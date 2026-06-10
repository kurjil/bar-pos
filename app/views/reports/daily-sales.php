<?php use App\Helpers\Formatter; include __DIR__ . '/_filter.php'; $reportForm('daily-sales', 'daily-sales'); ?>
<h2 class="h4 mb-3">Daily Sales Report</h2>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <div class="text-muted small">Today's Sales</div>
                <div class="h3 mb-0"><?= Formatter::money((float) $today['total']) ?></div>
                <small class="text-muted"><?= (int) $today['count'] ?> transactions</small>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($detail)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><strong>Sales by Date & Payment Method</strong></div>
    <div class="table-responsive"><table class="table mb-0 small">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th class="text-end">Transactions</th>
                <th class="text-end">Cash</th>
                <th class="text-end">Mobile Money</th>
                <th class="text-end">Card</th>
                <th class="text-end">Total Sales</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Tax</th>
            </tr>
        </thead>
        <tbody>
            <?php $totalSales = 0; $totalDiscount = 0; $totalTax = 0; ?>
            <?php foreach ($detail as $row): ?>
                <?php $totalSales += (float) $row['total']; $totalDiscount += (float) $row['total_discount']; $totalTax += (float) $row['total_tax']; ?>
                <tr>
                    <td><?= htmlspecialchars($row['date']) ?></td>
                    <td class="text-end"><?= (int) $row['transaction_count'] ?></td>
                    <td class="text-end"><span class="badge bg-success"><?= (int) $row['cash_count'] ?> / <?= Formatter::money((float) $row['cash_total']) ?></span></td>
                    <td class="text-end"><span class="badge bg-info"><?= (int) $row['mobile_count'] ?> / <?= Formatter::money((float) $row['mobile_total']) ?></span></td>
                    <td class="text-end"><span class="badge bg-secondary"><?= (int) $row['card_count'] ?> / <?= Formatter::money((float) $row['card_total']) ?></span></td>
                    <td class="text-end fw-bold"><?= Formatter::money((float) $row['total']) ?></td>
                    <td class="text-end text-danger"><?= Formatter::money((float) $row['total_discount']) ?></td>
                    <td class="text-end"><?= Formatter::money((float) $row['total_tax']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot class="table-light">
            <tr>
                <td colspan="5"><strong>TOTAL</strong></td>
                <td class="text-end fw-bold"><?= Formatter::money($totalSales) ?></td>
                <td class="text-end text-danger fw-bold"><?= Formatter::money($totalDiscount) ?></td>
                <td class="text-end fw-bold"><?= Formatter::money($totalTax) ?></td>
            </tr>
        </tfoot>
    </table></div>
</div>
<?php endif; ?>

<?php if (!empty($transactions)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><strong>Transactions</strong></div>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0">
            <thead class="table-light">
                <tr>
                    <th>Receipt #</th>
                    <th>Time</th>
                    <th>Cashier</th>
                    <th class="text-end">Items</th>
                    <th class="text-end">Total</th>
                    <th>Payment</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                <tr>
                    <td><a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $tx['id'] ?>"><?= e($tx['receipt_number']) ?></a></td>
                    <td><?= date('H:i:s', strtotime($tx['created_at'])) ?></td>
                    <td><?= e($tx['cashier_name']) ?></td>
                    <td class="text-end"><?= (int) $tx['items_count'] ?></td>
                    <td class="text-end"><?= Formatter::money((float) $tx['grand_total']) ?></td>
                    <td><?= e($tx['payment_method']) ?></td>
                    <td><a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $tx['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
