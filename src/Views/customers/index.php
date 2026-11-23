<?php /** @var array $items @var string $search */ $pageTitle = 'Customers | Bike Rental'; ?>
<div class="page-head"><h1>Customers</h1>
    <a class="btn btn-primary" href="<?= e(url('customer-form')) ?>">+ Add customer</a></div>
<form method="get" action="<?= e(url('customers')) ?>" class="filter-bar">
    <input type="hidden" name="route" value="customers">
    <div class="filter-group"><label for="search">Search</label>
        <input id="search" name="search" type="search" maxlength="50" class="form-control"
               placeholder="Name, email or phone..." value="<?= e($search) ?>"></div>
    <button class="btn btn-secondary" type="submit">Search</button>
</form>
<p class="muted"><?= count($items) ?> customer(s)</p>
<?php if (!$items): ?><p class="empty">No customers found.</p><?php else: ?>
<div class="table-wrap"><table class="data-table">
    <thead><tr><th>Name</th><th>Phone</th><th>Email</th><th>Rentals</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($items as $c): ?>
        <tr>
            <td><a href="<?= e(url('customer-show', ['id' => $c['customer_id']])) ?>"><?= e($c['full_name']) ?></a></td>
            <td><?= e($c['phone']) ?></td>
            <td><?= e($c['email']) ?></td>
            <td><?= (int)$c['rental_count'] ?></td>
            <td class="actions">
                <a class="btn btn-small" href="<?= e(url('customer-show', ['id' => $c['customer_id']])) ?>">View</a>
                <a class="btn btn-small" href="<?= e(url('customer-form', ['id' => $c['customer_id']])) ?>">Edit</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div>
<?php endif; ?>
