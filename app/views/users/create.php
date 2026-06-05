<h2 class="h4 mb-3">Create User</h2>
<form method="POST" action="<?= e(appConfig('url')) ?>/users/store">
    <?= csrfField() ?>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Name</label><input name="name" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Role</label><select name="role" class="form-select" required><option value="CASHIER">Cashier</option><option value="ADMIN">Admin</option></select></div>
        <div class="col-md-6"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required minlength="8"></div>
        <div class="col-md-6"><label class="form-label">Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Create</button> <a href="<?= e(appConfig('url')) ?>/users" class="btn btn-outline-secondary">Cancel</a></div>
</form>
