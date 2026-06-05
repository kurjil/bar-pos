<h2 class="h4 mb-3">Stock Adjustment</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/inventory/adjustments">
    <?= csrfField() ?>
    <div class="mb-3"><label class="form-label">Product</label>
        <select name="product_id" class="form-select" required>
            <?php foreach ($products as $p): ?>
                <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> (Current: <?= (int) $p['stock_quantity'] ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3"><label class="form-label">New Quantity</label><input type="number" name="new_quantity" class="form-control" min="0" required></div>
    <div class="mb-3"><label class="form-label">Reason (required)</label><textarea name="notes" class="form-control" required></textarea></div>
    <button class="btn btn-primary">Apply Adjustment</button>
</form>
