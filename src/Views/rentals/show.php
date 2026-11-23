<?php /** @var array $rental */ $pageTitle = 'Rental #' . $rental['rental_id'] . ' | Bike Rental'; ?>
<div class="page-head"><h1>Rental #<?= (int)$rental['rental_id'] ?></h1>
    <a class="btn" href="<?= e(url('rentals')) ?>">&larr; All rentals</a></div>
<section class="card">
    <dl class="details-list">
        <dt>Bike</dt><dd><a href="<?= e(url('bike-show', ['id' => $rental['bike_id']])) ?>"><?= e($rental['bike_code']) ?></a> &ndash; <?= e($rental['bike_model']) ?> (<?= e(money($rental['hourly_rate'])) ?>/h)</dd>
        <dt>Customer</dt><dd><a href="<?= e(url('customer-show', ['id' => $rental['customer_id']])) ?>"><?= e($rental['customer_name']) ?></a> &middot; <?= e($rental['customer_phone']) ?></dd>
        <dt>Started</dt><dd><?= e($rental['start_time']) ?></dd>
        <dt>Ended</dt><dd><?= $rental['end_time'] ? e($rental['end_time']) : '&ndash;' ?></dd>
        <dt>Total cost</dt><dd><?= $rental['total_cost'] !== null ? e(money($rental['total_cost'])) : 'Calculated on return' ?></dd>
        <dt>Status</dt><dd><?= status_badge($rental['status']) ?></dd>
    </dl>
    <div class="actions">
        <?php if ($rental['status'] === 'active'): ?>
            <?= post_button('rental-return', (int)$rental['rental_id'], 'Return bike', 'btn btn-primary', 'Return the bike now and calculate the cost?') ?>
            <?= post_button('rental-cancel', (int)$rental['rental_id'], 'Cancel rental', 'btn', 'Cancel this rental?') ?>
        <?php endif; ?>
        <a class="btn" href="<?= e(url('rental-form', ['id' => $rental['rental_id']])) ?>">Edit</a>
        <?= post_button('rental-delete', (int)$rental['rental_id'], 'Delete', 'btn btn-danger', 'Delete rental #' . $rental['rental_id'] . '?') ?>
    </div>
</section>
<p class="muted">Billing rule: every started hour is billed, minimum one hour.</p>
