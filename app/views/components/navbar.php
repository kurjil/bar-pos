<nav class="navbar navbar-light bg-white border-bottom px-4 py-3">
    <span class="navbar-brand mb-0 h5"><?= e($title ?? 'Dashboard') ?></span>
    <div class="d-flex align-items-center gap-3">
        <span class="text-muted small"><?= e(session()->get('user_name', '')) ?></span>
        <form action="<?= e(appConfig('url')) ?>/logout" method="POST" class="d-inline">
            <?= csrfField() ?>
            <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
        </form>
    </div>
</nav>
