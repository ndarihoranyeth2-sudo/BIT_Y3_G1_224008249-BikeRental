<?php /** @var array $errors @var array $old */ $pageTitle = 'Login | Bike Rental'; ?>
<section class="card narrow">
    <h1>Staff login</h1>
    <?php if (isset($errors['form'])): ?><div class="alert alert-error"><?= e($errors['form']) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('login')) ?>" novalidate data-validate>
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required autocomplete="username"
                   value="<?= old($old, 'username') ?>" class="form-control<?= err_class($errors, 'username') ?>">
            <?= field_error($errors, 'username') ?>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
                   class="form-control<?= err_class($errors, 'password') ?>">
            <?= field_error($errors, 'password') ?>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Log in</button>
    </form>
    <p class="muted">No account? <a href="<?= e(url('register')) ?>">Register</a></p>
</section>
