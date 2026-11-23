<?php /** @var array $items @var string $status */ $pageTitle = 'Rentals | Bike Rental'; ?>
<div class="page-head"><h1>Rentals</h1>
    <a class="btn btn-primary" href="<?= e(url('rental-form')) ?>">+ New rental</a></div>
<form method="get" action="<?= e(url('rentals')) ?>" class="filter-bar">
    <input type="hidden" name="route" value="rentals">
    <div class="filter-group"><label for="status">Status</label>
        <select id="status" name="status" class="form-control">
            <option value="">All</option>
            <?php foreach (\App\Models\Rental::STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
            <?php endforeach; ?></select></div>
    <button class="btn btn-secondary" type="submit">Filter</button>
</form>
<?php if (!$items): ?><p class="empty">No rentals found.</p><?php else: ?>
<div class="table-wrap"><table class="data-table">
    <thead><tr><th>#</th><th>Bike</th><th>Customer</th><th>Start</th><th>End</th><th>Cost</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($items as $r): ?>
        <tr>
            <td><?= (int)$r['rental_id'] ?></td>
            <td><?= e($r['bike_code']) ?></td>
            <td><?= e($r['customer_name']) ?></td>
            <td><?= e($r['start_time']) ?></td>
            <td><?= $r['end_time'] ? e($r['end_time']) : '&ndash;' ?></td>
            <td><?= $r['total_cost'] !== null ? e(money($r['total_cost'])) : '&ndash;' ?></td>
            <td><?= status_badge($r['status']) ?></td>
            <td class="actions"><a class="btn btn-small" href="<?= e(url('rental-show', ['id' => $r['rental_id']])) ?>">View</a></td>
        </tr>
    <?php endforeach; ?></tbody></table></div>
<?php endif; ?>
