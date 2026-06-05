<?php use App\Helpers\Formatter; ?>
<div class="d-flex justify-content-between mb-3"><h2 class="h4 mb-0">Sales</h2></div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Receipt</th><th>Cashier</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($sales as $s): ?>
                <tr>
                    <td><?= e($s['receipt_number']) ?></td>
                    <td><?= e($s['cashier_name']) ?></td>
                    <td><?= Formatter::money((float) $s['grand_total']) ?></td>
                    <td><?= e($s['payment_method']) ?></td>
                    <td><span class="badge bg-<?= $s['status'] === 'COMPLETED' ? 'success' : 'danger' ?>"><?= e($s['status']) ?></span></td>
                    <td><?= Formatter::datetime($s['created_at']) ?></td>
                    <td><a href="<?= e(appConfig('url')) ?>/sales/<?= (int) $s['id'] ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
