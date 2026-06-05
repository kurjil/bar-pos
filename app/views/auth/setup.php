<h2 class="h5 mb-2">Initial Setup</h2>
<p class="text-muted small mb-4">Create the first administrator account to get started.</p>

<form method="POST" action="<?= e(appConfig('url')) ?>/setup" novalidate>
    <?= csrfField() ?>

    <div class="mb-3">
        <label for="name" class="form-label">Full Name</label>
        <input type="text" class="form-control form-control-lg" id="name" name="name"
               value="<?= old('name') ?>" required autofocus>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control form-control-lg" id="email" name="email"
               value="<?= old('email') ?>" required autocomplete="email">
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control form-control-lg" id="password" name="password"
               required minlength="8" autocomplete="new-password">
        <div class="form-text">Minimum 8 characters.</div>
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label">Confirm Password</label>
        <input type="password" class="form-control form-control-lg" id="password_confirmation"
               name="password_confirmation" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">Create Admin Account</button>
</form>
