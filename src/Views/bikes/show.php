<?php /** @var array $bike */ $pageTitle = 'Bike ' . $bike['code'] . ' | Bike Rental'; ?>
<div class="page-head"><h1><?= e($bike['code']) ?> &ndash; <?= e($bike['model']) ?></h1>
    <a class="btn" href="<?= e(url('bikes')) ?>">&larr; All bikes</a></div>
<section class="card">
    <dl class="details-list">
        <dt>Code</dt><dd><?= e($bike['code']) ?></dd>
        <dt>Model</dt><dd><?= e($bike['model']) ?></dd>
        <dt>Hourly rate</dt><dd><?= e(money($bike['hourly_rate'])) ?></dd>
        <dt>Status</dt><dd><?= status_badge($bike['status']) ?></dd>
    </dl>
    <div class="actions">
        <?php if ($bike['status'] === 'available'): ?>
            <a class="btn btn-primary" href="<?= e(url('rental-form', ['bike_id' => $bike['bike_id']])) ?>">Rent this bike</a>
        <?php endif; ?>
        <a class="btn" href="<?= e(url('bike-form', ['id' => $bike['bike_id']])) ?>">Edit</a>
        <?= post_button('bike-delete', (int)$bike['bike_id'], 'Delete', 'btn btn-danger', 'Delete bike ' . $bike['code'] . '? This cannot be undone.') ?>
    </div>
</section>
