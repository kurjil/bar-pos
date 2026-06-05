<h2 class="h4 mb-3">Add Expense</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/expenses/store" enctype="multipart/form-data">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Category</label><input name="category" class="form-control" required placeholder="e.g. Supplies"></div>
        <div class="col-md-4"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required min="0.01"></div>
        <div class="col-md-4"><label class="form-label">Date</label><input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
        <div class="col-12"><label class="form-label">Description</label><input name="description" class="form-control" required></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
        <div class="col-12"><label class="form-label">Receipt Photo</label><input type="file" name="receipt_photo" class="form-control" accept="image/*"></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save</button> <a href="<?= e(appConfig('url')) ?>/expenses" class="btn btn-outline-secondary">Cancel</a></div>
</form>
