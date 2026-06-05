<div class="d-flex justify-content-between mb-3">
    <h2 class="h4 mb-0">Users</h2>
    <a href="<?= e(appConfig('url')) ?>/users/create" class="btn btn-primary">Add User</a>
</div>
<div class="card border-0 shadow-sm"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th></th></tr></thead>
    <tbody><?php foreach ($users as $u): ?><tr>
        <td><?= e($u['name']) ?></td><td><?= e($u['email']) ?></td><td><?= e($u['role_name']) ?></td><td><?= (int)$u['active'] ? 'Yes' : 'No' ?></td>
        <td class="text-end"><a href="<?= e(appConfig('url')) ?>/users/<?= (int)$u['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
        <?php if ((int)$u['id'] !== auth()->id()): ?><form method="POST" action="<?= e(appConfig('url')) ?>/users/<?= (int)$u['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Disable user?')"><?= csrfField() ?><button class="btn btn-sm btn-outline-danger">Disable</button></form><?php endif; ?>
        </td></tr><?php endforeach; ?></tbody>
</table></div></div>
