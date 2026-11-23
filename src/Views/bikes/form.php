<?php /** @var array $old @var array $errors @var int $id */
$pageTitle = ($id ? 'Edit bike' : 'Add bike') . ' | Bike Rental'; ?>
<h1><?= $id ? 'Edit bike' : 'Add bike' ?></h1>
<section class="card narrow">
<form method="post" action="<?= e(url('bike-save')) ?>" novalidate data-validate>
    <?= csrf_field() ?>
    <input type="hidden" name="bike_id" value="<?= (int)$id ?>">
    <div class="form-group">
        <label for="code">Code *</label>
        <input id="code" name="code" required pattern="[A-Za-z0-9\-]{3,20}" maxlength="20"
               data-msg-pattern="3-20 characters: letters, digits, hyphen (e.g. BK-009)."
               value="<?= old($old, 'code') ?>" class="form-control<?= err_class($errors, 'code') ?>">
        <?= field_error($errors, 'code') ?>
    </div>
    <div class="form-group">
        <label for="model">Model *</label>
        <input id="model" name="model" required minlength="2" maxlength="80"
               value="<?= old($old, 'model') ?>" class="form-control<?= err_class($errors, 'model') ?>">
        <?= field_error($errors, 'model') ?>
    </div>
    <div class="form-group">
        <label for="hourly_rate">Hourly rate (RWF) *</label>
        <input id="hourly_rate" name="hourly_rate" type="number" step="0.01" min="0.01" required
               value="<?= old($old, 'hourly_rate') ?>" class="form-control<?= err_class($errors, 'hourly_rate') ?>">
        <?= field_error($errors, 'hourly_rate') ?>
    </div>
    <div class="form-group">
        <label for="status">Status *</label>
        <select id="status" name="status" required class="form-control<?= err_class($errors, 'status') ?>">
            <?php foreach (\App\Models\Bike::STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= ($old['status'] ?? '') === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
            <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'status') ?>
    </div>
    <div class="actions">
        <button type="submit" class="btn btn-primary">Save</button>
        <a class="btn" href="<?= e(url('bikes')) ?>">Cancel</a>
    </div>
</form>
</section>
