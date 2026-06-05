<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Bar POS') ?> | <?= e(appConfig('name')) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(appConfig('url')) ?>/assets/css/app.css" rel="stylesheet">
</head>
<body class="auth-body">
    <main class="auth-container">
        <div class="auth-card card shadow">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-1"><?= e(appConfig('name')) ?></h1>
                    <p class="text-muted small mb-0">Point of Sale & Inventory</p>
                </div>

                <?php require dirname(__DIR__) . '/components/alerts.php'; ?>

                <?= $content ?>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
