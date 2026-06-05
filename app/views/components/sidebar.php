<?php
$currentPath = request()->uri();
$base = appConfig('url');
$isAdmin = auth()->role() === ROLE_ADMIN;

function navActive(string $path, string $current): string {
    return str_starts_with($current, $path) ? ' active' : '';
}
?>
<aside class="sidebar d-flex flex-column">
    <div class="sidebar-brand p-4">
        <h2 class="h5 text-white mb-0"><?= e(appConfig('name')) ?></h2>
    </div>
    <nav class="sidebar-nav flex-grow-1 px-3 pb-3">
        <a href="<?= e($base) ?>/dashboard" class="sidebar-link<?= navActive('/dashboard', $currentPath) ?>">Dashboard</a>
        <a href="<?= e($base) ?>/pos" class="sidebar-link<?= navActive('/pos', $currentPath) ?>">Point of Sale</a>
        <a href="<?= e($base) ?>/shifts/open" class="sidebar-link<?= navActive('/shifts', $currentPath) ?>">Shifts</a>
        <a href="<?= e($base) ?>/sales" class="sidebar-link<?= navActive('/sales', $currentPath) ?>">Sales</a>

        <?php if ($isAdmin): ?>
            <hr class="border-secondary my-2">
            <small class="text-white-50 px-3">Inventory</small>
            <a href="<?= e($base) ?>/products" class="sidebar-link<?= navActive('/products', $currentPath) ?>">Products</a>
            <a href="<?= e($base) ?>/categories" class="sidebar-link<?= navActive('/categories', $currentPath) ?>">Categories</a>
            <a href="<?= e($base) ?>/inventory/stock-in" class="sidebar-link<?= navActive('/inventory', $currentPath) ?>">Inventory</a>
            <a href="<?= e($base) ?>/expenses" class="sidebar-link<?= navActive('/expenses', $currentPath) ?>">Expenses</a>
            <hr class="border-secondary my-2">
            <small class="text-white-50 px-3">Reports</small>
            <a href="<?= e($base) ?>/reports/daily-sales" class="sidebar-link<?= navActive('/reports', $currentPath) ?>">Reports</a>
            <hr class="border-secondary my-2">
            <small class="text-white-50 px-3">Admin</small>
            <a href="<?= e($base) ?>/users" class="sidebar-link<?= navActive('/users', $currentPath) ?>">Users</a>
            <a href="<?= e($base) ?>/settings/general" class="sidebar-link<?= navActive('/settings', $currentPath) ?>">Settings</a>
        <?php endif; ?>
    </nav>
</aside>
