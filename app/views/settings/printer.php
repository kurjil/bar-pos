<h2 class="h4 mb-3">Printer Settings</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/settings/printer">
    <?= csrfField() ?>
    <div class="form-check mb-3"><input type="checkbox" name="enabled" class="form-check-input" id="enabled" <?= ($config['enabled'] ?? false) ? 'checked' : '' ?>><label for="enabled" class="form-check-label">Enable thermal printer</label></div>
    <div class="mb-3"><label class="form-label">Connector</label><select name="connector" class="form-select"><option value="windows" <?= ($config['connector'] ?? '') === 'windows' ? 'selected' : '' ?>>Windows (USB/Shared)</option><option value="network" <?= ($config['connector'] ?? '') === 'network' ? 'selected' : '' ?>>Network</option></select></div>
    <div class="mb-3"><label class="form-label">Windows Printer Name</label><input name="path" class="form-control" value="<?= e($config['path'] ?? '') ?>" placeholder="EPSON TM-T20 Receipt"></div>
    <div class="row g-3">
        <div class="col-md-8"><label class="form-label">Network Host</label><input name="host" class="form-control" value="<?= e($config['host'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="form-label">Port</label><input name="port" class="form-control" value="<?= e((string) ($config['port'] ?? 9100)) ?>"></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save</button></div>
</form>
<form method="POST" action="<?= e(appConfig('url')) ?>/settings/printer/test" class="mt-3"><?= csrfField() ?><button class="btn btn-outline-secondary">Test Print</button></form>
