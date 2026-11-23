<?php /** @var array $bikeCounts $stats $recent @var int $customers */ $pageTitle = 'Dashboard | Bike Rental'; ?>
<h1>Dashboard</h1>
<div class="stats-grid">
    <div class="stat-card"><span class="stat-number"><?= array_sum($bikeCounts) ?></span><span class="stat-label">Bikes in fleet</span></div>
    <div class="stat-card"><span class="stat-number"><?= (int)$bikeCounts['available'] ?></span><span class="stat-label">Available</span></div>
    <div class="stat-card"><span class="stat-number"><?= (int)$stats['active'] ?></span><span class="stat-label">Active rentals</span></div>
    <div class="stat-card"><span class="stat-number"><?= (int)$customers ?></span><span class="stat-label">Customers</span></div>
    <div class="stat-card"><span class="stat-number"><?= e(money($stats['revenue'])) ?></span><span class="stat-label">Revenue (completed)</span></div>
</div>

<section class="card">
    <div class="card-head"><h2>Recent rentals</h2>
        <a class="btn btn-primary btn-small" href="<?= e(url('rental-form')) ?>">+ New rental</a></div>
    <?php if (!$recent): ?><p class="muted">No rentals yet.</p><?php else: ?>
    <div class="table-wrap"><table class="data-table">
        <thead><tr><th>#</th><th>Bike</th><th>Customer</th><th>Started</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $r): ?>
            <tr>
                <td><a href="<?= e(url('rental-show', ['id' => $r['rental_id']])) ?>"><?= (int)$r['rental_id'] ?></a></td>
                <td><?= e($r['bike_code']) ?> &ndash; <?= e($r['bike_model']) ?></td>
                <td><?= e($r['customer_name']) ?></td>
                <td><?= e($r['start_time']) ?></td>
                <td><?= status_badge($r['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
