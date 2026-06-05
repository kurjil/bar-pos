<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'POS') ?> | <?= e(appConfig('name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(appConfig('url')) ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="pos-body">
    <header class="pos-header d-flex justify-content-between align-items-center px-3 py-2">
        <div>
            <strong><?= e(appConfig('name')) ?> — POS</strong>
            <?php if (!empty($shift)): ?>
                <span class="badge bg-success ms-2">Shift #<?= (int) $shift['id'] ?></span>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <span class="text-white-50 small"><?= e(session()->get('user_name')) ?></span>
            <a href="<?= e(appConfig('url')) ?>/dashboard" class="btn btn-outline-light btn-sm">Exit POS</a>
        </div>
    </header>
    <?= $content ?>
    <script>
        window.APP_URL = <?= json_encode(appConfig('url')) ?>;
        window.CSRF_TOKEN = <?= json_encode(session()->get('csrf_token')) ?>;
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(appConfig('url')) ?>/assets/js/pos.js"></script>
</body>
</html>
