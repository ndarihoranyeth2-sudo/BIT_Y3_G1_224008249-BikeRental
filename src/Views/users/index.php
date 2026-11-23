<?php /** @var array $items */ $pageTitle = 'Users | Bike Rental'; ?>
<div class="page-head"><h1>Users</h1>
    <a class="btn btn-primary" href="<?= e(url('user-form')) ?>">+ Add user</a></div>
<div class="table-wrap"><table class="data-table">
    <thead><tr><th>Username</th><th>Email</th><th>Role</th><th>Created</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($items as $u): ?>
        <tr>
            <td><?= e($u['username']) ?></td><td><?= e($u['email']) ?></td>
            <td><?= status_badge($u['role']) ?></td><td><?= e($u['created_at']) ?></td>
            <td class="actions">
                <a class="btn btn-small" href="<?= e(url('user-form', ['id' => $u['user_id']])) ?>">Edit</a>
                <?= post_button('user-delete', (int)$u['user_id'], 'Delete', 'btn btn-small btn-danger', 'Delete user ' . $u['username'] . '?') ?>
            </td>
        </tr>
    <?php endforeach; ?></tbody></table></div>
