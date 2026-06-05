<?php use App\Helpers\Formatter; ?>
<h2 class="h4 mb-3">Inventory History</h2>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Qty</th><th>User</th><th>Notes</th></tr></thead>
            <tbody>
            <?php foreach ($movements as $m): ?>
                <tr>
                    <td><?= Formatter::datetime($m['created_at']) ?></td>
                    <td><?= e($m['product_name']) ?></td>
                    <td><span class="badge bg-secondary"><?= e($m['movement_type']) ?></span></td>
                    <td><?= (int) $m['quantity'] ?></td>
                    <td><?= e($m['user_name']) ?></td>
                    <td><?= e($m['notes'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
