<div class="d-flex justify-content-between mb-3">
    <h2 class="h4 mb-0">Categories</h2>
    <a href="<?= e(appConfig('url')) ?>/categories/create" class="btn btn-primary">Add Category</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>Description</th><th>Active</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= e($c['name']) ?></td>
                    <td><?= e($c['description'] ?? '') ?></td>
                    <td><?= (int) $c['active'] ? 'Yes' : 'No' ?></td>
                    <td class="text-end">
                        <a href="<?= e(appConfig('url')) ?>/categories/<?= (int) $c['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="POST" action="<?= e(appConfig('url')) ?>/categories/<?= (int) $c['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete?')">
                            <?= csrfField() ?><button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
