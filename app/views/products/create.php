<h2 class="h4 mb-3">Add Product</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/products/store" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">Select...</option>
                <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Purchase Price</label><input type="number" step="0.01" name="purchase_price" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Selling Price</label><input type="number" step="0.01" name="selling_price" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Stock Qty</label><input type="number" name="stock_quantity" class="form-control" value="0"></div>
        <div class="col-md-4"><label class="form-label">Min Stock</label><input type="number" name="minimum_stock" class="form-control" value="5"></div>
        <div class="col-md-4"><label class="form-label">Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control"></textarea></div>
        <div class="col-12 form-check"><input type="checkbox" name="is_favorite" class="form-check-input" id="fav"><label for="fav" class="form-check-label">Favorite (POS shortcut)</label></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="<?= e(appConfig('url')) ?>/products" class="btn btn-outline-secondary">Cancel</a></div>
</form>
