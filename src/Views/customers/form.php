<?php /** @var array $old @var array $errors @var int $id */
$pageTitle = ($id ? 'Edit customer' : 'Add customer') . ' | Bike Rental'; ?>
<h1><?= $id ? 'Edit customer' : 'Add customer' ?></h1>
<section class="card narrow">
<form method="post" action="<?= e(url('customer-save')) ?>" novalidate data-validate>
    <?= csrf_field() ?>
    <input type="hidden" name="customer_id" value="<?= (int)$id ?>">
    <div class="form-group"><label for="full_name">Full name *</label>
        <input id="full_name" name="full_name" required minlength="3" maxlength="100"
               value="<?= old($old, 'full_name') ?>" class="form-control<?= err_class($errors, 'full_name') ?>">
        <?= field_error($errors, 'full_name') ?></div>
    <div class="form-group"><label for="phone">Phone *</label>
        <input id="phone" name="phone" required pattern="\+?[0-9 ]{7,15}" placeholder="+250788123456"
               data-msg-pattern="7-15 digits, optional leading +."
               value="<?= old($old, 'phone') ?>" class="form-control<?= err_class($errors, 'phone') ?>">
        <?= field_error($errors, 'phone') ?></div>
    <div class="form-group"><label for="email">Email *</label>
        <input id="email" name="email" type="email" required maxlength="100"
               value="<?= old($old, 'email') ?>" class="form-control<?= err_class($errors, 'email') ?>">
        <?= field_error($errors, 'email') ?></div>
    <div class="actions"><button type="submit" class="btn btn-primary">Save</button>
        <a class="btn" href="<?= e(url('customers')) ?>">Cancel</a></div>
</form></section>
