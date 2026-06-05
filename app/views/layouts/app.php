<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Bar POS') ?> | <?= e(appConfig('name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(appConfig('url')) ?>/assets/css/app.css" rel="stylesheet">
</head>
<body>
    <div class="app-wrapper d-flex">
        <?php require dirname(__DIR__) . '/components/sidebar.php'; ?>

        <div class="app-content flex-grow-1">
            <?php require dirname(__DIR__) . '/components/navbar.php'; ?>

            <main class="p-4">
                <?php require dirname(__DIR__) . '/components/alerts.php'; ?>
                <?= $content ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= e(appConfig('url')) ?>/assets/js/app.js"></script>
    <script src="<?= e(appConfig('url')) ?>/assets/js/dashboard.js"></script>
</body>
</html>
