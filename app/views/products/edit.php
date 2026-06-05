<h2 class="h4 mb-3">Edit Product</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/products/<?= (int) $product['id'] ?>/update" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= e($product['name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === (int) $product['category_id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6"><label class="form-label">Purchase Price</label><input type="number" step="0.01" name="purchase_price" class="form-control" value="<?= e((string) $product['purchase_price']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Selling Price</label><input type="number" step="0.01" name="selling_price" class="form-control" value="<?= e((string) $product['selling_price']) ?>" required></div>
        <div class="col-md-4"><label class="form-label">Min Stock</label><input type="number" name="minimum_stock" class="form-control" value="<?= (int) $product['minimum_stock'] ?>"></div>
        <div class="col-md-4"><label class="form-label">Current Stock</label><input class="form-control" value="<?= (int) $product['stock_quantity'] ?>" disabled></div>
        <div class="col-md-4"><label class="form-label">New Image</label><input type="file" name="image" class="form-control" accept="image/*"></div>
        <div class="col-12 form-check"><input type="checkbox" name="active" class="form-check-input" id="active" <?= (int) $product['active'] ? 'checked' : '' ?>><label for="active" class="form-check-label">Active</label></div>
        <div class="col-12 form-check"><input type="checkbox" name="is_favorite" class="form-check-input" id="fav" <?= (int) $product['is_favorite'] ? 'checked' : '' ?>><label for="fav" class="form-check-label">Favorite</label></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Update</button> <a href="<?= e(appConfig('url')) ?>/products" class="btn btn-outline-secondary">Cancel</a></div>
</form>
