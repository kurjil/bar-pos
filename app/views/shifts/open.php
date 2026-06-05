<h2 class="h4 mb-3">Open Shift</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/shifts/open">
    <?= csrfField() ?>
    <div class="mb-3"><label class="form-label">Opening Float (Cash in drawer)</label>
        <input type="number" step="0.01" name="opening_float" class="form-control form-control-lg" required min="0" autofocus></div>
    <button class="btn btn-primary btn-lg">Open Shift & Go to POS</button>
</form>
