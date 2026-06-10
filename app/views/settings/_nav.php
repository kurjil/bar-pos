<?php
$currentPath = request()->uri();
$base = appConfig('url');

function settingsTabActive(string $path, string $current): string
{
    return str_starts_with($current, $path) ? ' active' : '';
}
?>
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link<?= settingsTabActive('/settings/general', $currentPath) ?>" href="<?= e($base) ?>/settings/general">General</a>
    </li>
    <li class="nav-item">
        <a class="nav-link<?= settingsTabActive('/settings/printer', $currentPath) ?>" href="<?= e($base) ?>/settings/printer">Printer</a>
    </li>
    <li class="nav-item">
        <a class="nav-link<?= settingsTabActive('/settings/backup', $currentPath) ?>" href="<?= e($base) ?>/settings/backup">Backup</a>
    </li>
</ul>
