<h2 class="h4 mb-4">Register New User</h2>

<form method="POST" action="<?= e(appConfig('url')) ?>/users/register" novalidate>
    <?= csrfField() ?>

    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label">Full Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="col-md-6">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role" required>
                <option value="">Select role...</option>
                <option value="<?= e(ROLE_ADMIN) ?>">Admin</option>
                <option value="<?= e(ROLE_CASHIER) ?>">Cashier</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password"
                   required minlength="8" autocomplete="new-password">
        </div>

        <div class="col-md-6">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" class="form-control" id="password_confirmation"
                   name="password_confirmation" required autocomplete="new-password">
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">Create User</button>
        <a href="<?= e(appConfig('url')) ?>/dashboard" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
