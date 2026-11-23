<?php /** @var array $old @var array $errors @var int $id */
$pageTitle = ($id ? 'Edit user' : 'Add user') . ' | Bike Rental'; ?>
<h1><?= $id ? 'Edit user' : 'Add user' ?></h1>
<section class="card narrow">
<form method="post" action="<?= e(url('user-save')) ?>" novalidate data-validate>
    <?= csrf_field() ?>
    <input type="hidden" name="user_id" value="<?= (int)$id ?>">
    <div class="form-group"><label for="username">Username *</label>
        <input id="username" name="username" required pattern="[A-Za-z0-9_]{3,30}"
               data-msg-pattern="3-30 characters: letters, digits, underscore."
               value="<?= old($old, 'username') ?>" class="form-control<?= err_class($errors, 'username') ?>">
        <?= field_error($errors, 'username') ?></div>
    <div class="form-group"><label for="email">Email *</label>
        <input id="email" name="email" type="email" required maxlength="100"
               value="<?= old($old, 'email') ?>" class="form-control<?= err_class($errors, 'email') ?>">
        <?= field_error($errors, 'email') ?></div>
    <div class="form-group"><label for="password">Password <?= $id ? '(leave blank to keep current)' : '*' ?></label>
        <input id="password" name="password" type="password" minlength="8" autocomplete="new-password"
               <?= $id ? '' : 'required' ?> class="form-control<?= err_class($errors, 'password') ?>">
        <?= field_error($errors, 'password') ?></div>
    <div class="form-group"><label for="role">Role *</label>
        <select id="role" name="role" required class="form-control<?= err_class($errors, 'role') ?>">
            <?php foreach (['staff', 'admin'] as $r): ?>
                <option value="<?= $r ?>" <?= ($old['role'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
            <?php endforeach; ?></select>
        <?= field_error($errors, 'role') ?></div>
    <div class="actions"><button type="submit" class="btn btn-primary">Save</button>
        <a class="btn" href="<?= e(url('users')) ?>">Cancel</a></div>
</form></section>
