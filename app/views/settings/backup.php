<?php include __DIR__ . '/_nav.php'; ?>
<h2 class="h4 mb-3">Backup & Restore</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/settings/backup/create" class="mb-4"><?= csrfField() ?><button class="btn btn-primary">Create Backup Now</button></form>
<h5>Available Backups</h5>
<ul class="list-group mb-4">
    <?php foreach ($backups as $file): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= e($file) ?>
            <a href="<?= e(appConfig('url')) ?>/settings/backup/download/<?= e(urlencode($file)) ?>" class="btn btn-sm btn-outline-primary">Download</a>
        </li>
    <?php endforeach; ?>
    <?php if (empty($backups)): ?><li class="list-group-item text-muted">No backups yet</li><?php endif; ?>
</ul>
<h5>Restore from File</h5>
<form method="POST" action="<?= e(appConfig('url')) ?>/settings/backup/restore" enctype="multipart/form-data" onsubmit="return confirm('This will overwrite the database. Continue?')">
    <?= csrfField() ?>
    <input type="file" name="backup_file" class="form-control mb-2" accept=".sql" required>
    <button class="btn btn-danger">Restore Database</button>
</form>
