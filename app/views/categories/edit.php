<h2 class="h4 mb-3">Edit Category</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/categories/<?= (int) $category['id'] ?>/update">
    <?= csrfField() ?>
    <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= e($category['name']) ?>" required></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control"><?= e($category['description'] ?? '') ?></textarea></div>
    <div class="form-check mb-3"><input type="checkbox" name="active" class="form-check-input" id="active" <?= (int) $category['active'] ? 'checked' : '' ?>><label class="form-check-label" for="active">Active</label></div>
    <button class="btn btn-primary">Update</button>
    <a href="<?= e(appConfig('url')) ?>/categories" class="btn btn-outline-secondary">Cancel</a>
</form>
