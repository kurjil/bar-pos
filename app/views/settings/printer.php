<?php include __DIR__ . '/_nav.php'; ?>
<h2 class="h4 mb-3">Printer Settings</h2>

<div class="alert alert-info">
    <strong>Setup:</strong> Check <strong>Enable thermal printer</strong>, enter your exact Windows printer name, save, then click <strong>Test Print</strong>.
    Find the name in Windows: <em>Settings → Bluetooth &amp; devices → Printers &amp; scanners</em>.
</div>

<form method="POST" action="<?= e(appConfig('url')) ?>/settings/printer">
    <?= csrfField() ?>
    <div class="form-check mb-3">
        <input type="checkbox" name="enabled" class="form-check-input" id="enabled" value="1" <?= ($config['enabled'] ?? false) ? 'checked' : '' ?>>
        <label for="enabled" class="form-check-label">Enable thermal printer</label>
    </div>
    <div class="mb-3">
        <label class="form-label">Connector Type</label>
        <select name="connector" id="connector" class="form-select">
            <option value="windows" <?= ($config['connector'] ?? 'windows') === 'windows' ? 'selected' : '' ?>>Windows (USB / shared printer)</option>
            <option value="network" <?= ($config['connector'] ?? '') === 'network' ? 'selected' : '' ?>>Network (Ethernet / Wi-Fi)</option>
        </select>
    </div>
    <div class="mb-3" id="windows-fields">
        <label class="form-label">Windows Printer Name</label>
        <input name="path" class="form-control" value="<?= e($config['path'] ?? '') ?>" placeholder="EPSON TM-T20 Receipt">
        <div class="form-text">Must match the name shown in Windows Printers &amp; scanners exactly.</div>
    </div>
    <div class="row g-3" id="network-fields">
        <div class="col-md-8">
            <label class="form-label">Network Host (IP address)</label>
            <input name="host" class="form-control" value="<?= e($config['host'] ?? '') ?>" placeholder="192.168.1.100">
        </div>
        <div class="col-md-4">
            <label class="form-label">Port</label>
            <input name="port" class="form-control" value="<?= e((string) ($config['port'] ?? 9100)) ?>">
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save Printer Settings</button>
    </div>
</form>

<form method="POST" action="<?= e(appConfig('url')) ?>/settings/printer/test" class="mt-3">
    <?= csrfField() ?>
    <button type="submit" class="btn btn-outline-secondary">Test Print</button>
</form>

<script>
(function () {
    var connector = document.getElementById('connector');
    var windowsFields = document.getElementById('windows-fields');
    var networkFields = document.getElementById('network-fields');

    function toggleFields() {
        var isNetwork = connector.value === 'network';
        windowsFields.style.display = isNetwork ? 'none' : 'block';
        networkFields.style.display = isNetwork ? 'flex' : 'none';
    }

    connector.addEventListener('change', toggleFields);
    toggleFields();
})();
</script>
