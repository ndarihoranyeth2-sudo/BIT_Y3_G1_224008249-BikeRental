<?php /** @var array $customer @var array $rentals */ $pageTitle = $customer['full_name'] . ' | Bike Rental'; ?>
<div class="page-head"><h1><?= e($customer['full_name']) ?></h1>
    <a class="btn" href="<?= e(url('customers')) ?>">&larr; All customers</a></div>
<section class="card">
    <dl class="details-list">
        <dt>Phone</dt><dd><?= e($customer['phone']) ?></dd>
        <dt>Email</dt><dd><?= e($customer['email']) ?></dd>
    </dl>
    <div class="actions">
        <a class="btn btn-primary" href="<?= e(url('rental-form')) ?>">New rental</a>
        <a class="btn" href="<?= e(url('customer-form', ['id' => $customer['customer_id']])) ?>">Edit</a>
        <?= post_button('customer-delete', (int)$customer['customer_id'], 'Delete', 'btn btn-danger', 'Delete customer ' . $customer['full_name'] . '?') ?>
    </div>
</section>
<section class="card"><h2>Rental history</h2>
<?php if (!$rentals): ?><p class="muted">No rentals yet.</p><?php else: ?>
<div class="table-wrap"><table class="data-table">
    <thead><tr><th>#</th><th>Bike</th><th>Started</th><th>Cost</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($rentals as $r): ?>
        <tr><td><a href="<?= e(url('rental-show', ['id' => $r['rental_id']])) ?>"><?= (int)$r['rental_id'] ?></a></td>
            <td><?= e($r['bike_code']) ?></td><td><?= e($r['start_time']) ?></td>
            <td><?= $r['total_cost'] !== null ? e(money($r['total_cost'])) : '&ndash;' ?></td>
            <td><?= status_badge($r['status']) ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
<?php endif; ?></section>
