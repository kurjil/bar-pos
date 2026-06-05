<?php use App\Helpers\Formatter; ?>
<div class="d-flex justify-content-between mb-3">
    <h2 class="h4 mb-0">Expenses</h2>
    <a href="<?= e(appConfig('url')) ?>/expenses/create" class="btn btn-primary">Add Expense</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Amount</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($expenses as $e): ?>
                <tr>
                    <td><?= e($e['expense_date']) ?></td>
                    <td><?= e($e['category']) ?></td>
                    <td><?= e($e['description']) ?></td>
                    <td><?= Formatter::money((float) $e['amount']) ?></td>
                    <td><span class="badge bg-<?= $e['status'] === 'APPROVED' ? 'success' : 'warning' ?>"><?= e($e['status']) ?></span></td>
                    <td>
                        <?php if ($e['status'] === 'PENDING'): ?>
                        <form method="POST" action="<?= e(appConfig('url')) ?>/expenses/<?= (int) $e['id'] ?>/approve" class="d-inline"><?= csrfField() ?><button class="btn btn-sm btn-success">Approve</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
