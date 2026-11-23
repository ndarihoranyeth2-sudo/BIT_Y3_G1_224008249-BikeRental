<?php /** @var array $old @var array $errors @var int $id @var ?array $rental @var array $customers @var array $bikes */
$pageTitle = ($id ? 'Edit rental' : 'New rental') . ' | Bike Rental'; ?>
<h1><?= $id ? 'Edit rental #' . (int)$id : 'New rental' ?></h1>
<section class="card narrow">
<form method="post" action="<?= e(url('rental-save')) ?>" novalidate data-validate>
    <?= csrf_field() ?>
    <input type="hidden" name="rental_id" value="<?= (int)$id ?>">
    <div class="form-group">
        <label for="bike_id">Bike *</label>
        <?php if ($id && $rental): ?>
            <input class="form-control" disabled value="<?= e($rental['bike_code'] . ' - ' . $rental['bike_model']) ?>">
            <small class="muted">The bike cannot be changed after the rental starts.</small>
        <?php else: ?>
            <select id="bike_id" name="bike_id" required class="form-control<?= err_class($errors, 'bike_id') ?>">
                <option value="">-- choose an available bike --</option>
                <?php foreach ($bikes as $b): ?>
                    <option value="<?= (int)$b['bike_id'] ?>" <?= (string)($old['bike_id'] ?? '') === (string)$b['bike_id'] ? 'selected' : '' ?>>
                        <?= e($b['code'] . ' - ' . $b['model'] . ' (' . money($b['hourly_rate']) . '/h)') ?></option>
                <?php endforeach; ?>
            </select>
            <?= field_error($errors, 'bike_id') ?>
        <?php endif; ?>
    </div>
    <div class="form-group">
        <label for="customer_id">Customer *</label>
        <select id="customer_id" name="customer_id" required class="form-control<?= err_class($errors, 'customer_id') ?>">
            <option value="">-- choose a customer --</option>
            <?php foreach ($customers as $c): ?>
                <option value="<?= (int)$c['customer_id'] ?>" <?= (string)($old['customer_id'] ?? '') === (string)$c['customer_id'] ? 'selected' : '' ?>><?= e($c['full_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'customer_id') ?>
    </div>
    <div class="form-group">
        <label for="start_time">Start time *</label>
        <input id="start_time" name="start_time" type="datetime-local" required
               value="<?= old($old, 'start_time') ?>" class="form-control<?= err_class($errors, 'start_time') ?>">
        <?= field_error($errors, 'start_time') ?>
    </div>
    <div class="actions"><button type="submit" class="btn btn-primary">Save</button>
        <a class="btn" href="<?= e(url('rentals')) ?>">Cancel</a></div>
</form></section>
