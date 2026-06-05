<h2 class="h4 mb-3">Stock In</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/inventory/stock-in">
    <?= csrfField() ?>
    <div class="mb-3"><label class="form-label">Product</label>
        <select name="product_id" class="form-select" required>
            <option value="">Select product...</option>
            <?php foreach ($products as $p): ?>
                <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> (Stock: <?= (int) $p['stock_quantity'] ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" class="form-control" min="1" required></div>
    <div class="mb-3"><label class="form-label">Cost Price</label><input type="number" step="0.01" name="cost_price" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
    <button class="btn btn-primary">Record Stock In</button>
</form>
