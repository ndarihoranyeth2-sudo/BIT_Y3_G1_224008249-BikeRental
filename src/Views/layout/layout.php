<?php /** @var string $content @var array $flash @var ?array $user */ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(\App\Support\Csrf::token()) ?>">
    <title><?= e($pageTitle ?? 'Online Bike Rental System') ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
<header class="main-header">
    <div class="container header-inner">
        <a class="brand" href="<?= e(url('dashboard')) ?>">&#128690; Bike Rental</a>
        <button class="nav-toggle" type="button" aria-label="Toggle menu" aria-expanded="false">&#9776;</button>
        <nav class="main-nav" id="main-nav">
            <?php if ($user): ?>
                <a href="<?= e(url('dashboard')) ?>">Dashboard</a>
                <a href="<?= e(url('bikes')) ?>">Bikes</a>
                <a href="<?= e(url('customers')) ?>">Customers</a>
                <a href="<?= e(url('rentals')) ?>">Rentals</a>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <a href="<?= e(url('users')) ?>">Users</a>
                <?php endif; ?>
                <form method="post" action="<?= e(url('logout')) ?>" class="inline-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-small btn-light">Logout (<?= e($user['username']) ?>)</button>
                </form>
            <?php else: ?>
                <a href="<?= e(url('login')) ?>">Login</a>
                <a href="<?= e(url('register')) ?>">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="container">
    <div id="toast-area" aria-live="polite">
        <?php foreach ($flash as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?>" role="alert"><?= e($f['message']) ?></div>
        <?php endforeach; ?>
    </div>
    <?= $content ?>
</main>

<footer class="main-footer">
    <div class="container">&copy; <?= date('Y') ?> Online Bike Rental System &middot; BIT Y3 Web Programming project</div>
</footer>
<script src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
