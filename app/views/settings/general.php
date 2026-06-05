<h2 class="h4 mb-3">General Settings</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/settings/general">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Business Name</label><input name="business_name" class="form-control" value="<?= e($settings['business_name'] ?? '') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input name="business_phone" class="form-control" value="<?= e($settings['business_phone'] ?? '') ?>"></div>
        <div class="col-12"><label class="form-label">Address</label><input name="business_address" class="form-control" value="<?= e($settings['business_address'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="form-label">Currency</label><input name="currency" class="form-control" value="<?= e($settings['currency'] ?? 'USD') ?>" required></div>
        <div class="col-md-4"><label class="form-label">Tax Rate (%)</label><input type="number" step="0.01" name="tax_rate" class="form-control" value="<?= e($settings['tax_rate'] ?? '0') ?>"></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save Settings</button></div>
</form>
