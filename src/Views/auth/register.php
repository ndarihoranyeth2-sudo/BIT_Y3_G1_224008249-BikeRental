<?php /** @var array $errors @var array $old */ $pageTitle = 'Register | Bike Rental'; ?>
<section class="card narrow">
    <h1>Create an account</h1>
    <form method="post" action="<?= e(url('register')) ?>" novalidate data-validate>
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required pattern="[A-Za-z0-9_]{3,30}"
                   data-msg-pattern="3-30 characters: letters, digits, underscore."
                   value="<?= old($old, 'username') ?>" class="form-control<?= err_class($errors, 'username') ?>">
            <?= field_error($errors, 'username') ?>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" required maxlength="100"
                   value="<?= old($old, 'email') ?>" class="form-control<?= err_class($errors, 'email') ?>">
            <?= field_error($errors, 'email') ?>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required minlength="8"
                   autocomplete="new-password" class="form-control<?= err_class($errors, 'password') ?>">
            <small class="muted">At least 8 characters, with a letter and a digit.</small>
            <?= field_error($errors, 'password') ?>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm password</label>
            <input id="password_confirm" name="password_confirm" type="password" required
                   autocomplete="new-password" data-match="password"
                   class="form-control<?= err_class($errors, 'password_confirm') ?>">
            <?= field_error($errors, 'password_confirm') ?>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>
    <p class="muted">Already registered? <a href="<?= e(url('login')) ?>">Log in</a></p>
</section>
