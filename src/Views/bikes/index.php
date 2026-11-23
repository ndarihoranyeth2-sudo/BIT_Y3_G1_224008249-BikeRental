<?php /** @var array $items @var string $search @var string $status */ $pageTitle = 'Bikes | Bike Rental'; ?>
<div class="page-head"><h1>Bikes</h1>
    <a class="btn btn-primary" href="<?= e(url('bike-form')) ?>">+ Add bike</a></div>

<!-- Works without JavaScript (server-rendered GET). JS upgrades it to live search via the JSON API. -->
<form method="get" action="<?= e(url('bikes')) ?>" class="filter-bar" id="bike-filter"
      data-api="<?= e(url('api-bikes')) ?>">
    <input type="hidden" name="route" value="bikes">
    <div class="filter-group">
        <label for="search">Search</label>
        <input id="search" name="search" type="search" maxlength="50" class="form-control"
               placeholder="Code or model..." value="<?= e($search) ?>" autocomplete="off">
    </div>
    <div class="filter-group">
        <label for="status">Status</label>
        <select id="status" name="status" class="form-control">
            <option value="">All</option>
            <?php foreach (\App\Models\Bike::STATUSES as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-secondary" type="submit">Filter</button>
</form>

<p id="bike-status" class="muted" aria-live="polite"><?= count($items) ?> bike(s) found</p>
<div class="table-wrap">
<table class="data-table" id="bike-table">
    <thead><tr><th>Code</th><th>Model</th><th>Rate / hour</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody id="bike-rows" data-show-url="<?= e(url('bike-show')) ?>" data-edit-url="<?= e(url('bike-form')) ?>">
    <?php foreach ($items as $b): ?>
        <tr>
            <td><a href="<?= e(url('bike-show', ['id' => $b['bike_id']])) ?>"><?= e($b['code']) ?></a></td>
            <td><?= e($b['model']) ?></td>
            <td><?= e(money($b['hourly_rate'])) ?></td>
            <td><?= status_badge($b['status']) ?></td>
            <td class="actions">
                <a class="btn btn-small" href="<?= e(url('bike-show', ['id' => $b['bike_id']])) ?>">View</a>
                <a class="btn btn-small" href="<?= e(url('bike-form', ['id' => $b['bike_id']])) ?>">Edit</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<p id="bike-empty" class="empty" <?= $items ? 'hidden' : '' ?>>No bikes match your search.</p>
