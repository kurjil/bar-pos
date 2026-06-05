<h2 class="h5 mb-3">Sign In</h2>

<form method="POST" action="<?= e(appConfig('url')) ?>/login" novalidate>
    <?= csrfField() ?>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control form-control-lg" id="email" name="email"
               value="<?= old('email') ?>" required autofocus autocomplete="email">
    </div>

    <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control form-control-lg" id="password" name="password"
               required autocomplete="current-password">
    </div>

    <button type="submit" class="btn btn-primary btn-lg w-100">Login</button>
</form>
