<h2 class="h4 mb-3">Add Category</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/categories/store">
    <?= csrfField() ?>
    <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control"></textarea></div>
    <button class="btn btn-primary">Save</button>
    <a href="<?= e(appConfig('url')) ?>/categories" class="btn btn-outline-secondary">Cancel</a>
</form>
