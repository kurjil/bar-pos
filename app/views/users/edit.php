<h2 class="h4 mb-3">Edit User</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/users/<?= (int) $user['id'] ?>/update">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= e($user['name']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select"><option value="ADMIN" <?= $user['role_name']==='ADMIN'?'selected':'' ?>>Admin</option><option value="CASHIER" <?= $user['role_name']==='CASHIER'?'selected':'' ?>>Cashier</option></select></div>
        <div class="col-md-6 form-check mt-4"><input type="checkbox" name="active" class="form-check-input" id="active" <?= (int)$user['active']?'checked':'' ?>><label for="active" class="form-check-label">Active</label></div>
        <div class="col-md-6"><label class="form-label">New Password (optional)</label><input type="password" name="password" class="form-control" minlength="8"></div>
        <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control"></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Update</button> <a href="<?= e(appConfig('url')) ?>/users" class="btn btn-outline-secondary">Cancel</a></div>
</form>
