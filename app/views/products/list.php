<?php use App\Helpers\Formatter; ?>
<div class="d-flex justify-content-between mb-3">
    <h2 class="h4 mb-0">Products</h2>
    <a href="<?= e(appConfig('url')) ?>/products/create" class="btn btn-primary">Add Product</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= e($p['name']) ?></td>
                    <td><?= e($p['category_name']) ?></td>
                    <td><?= Formatter::money((float) $p['selling_price']) ?></td>
                    <td><?= (int) $p['stock_quantity'] ?></td>
                    <td class="text-end">
                        <a href="<?= e(appConfig('url')) ?>/products/<?= (int) $p['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="POST" action="<?= e(appConfig('url')) ?>/products/<?= (int) $p['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete?')">
                            <?= csrfField() ?><button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
