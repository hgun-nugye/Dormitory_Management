<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? '') ?> | KTX ĐHQG</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/login.css">
</head>
<body>
<?php if ($view === 'login'): ?>
    <?php include __DIR__ . '/login.php'; ?>
<?php else: ?>
    <?php include __DIR__ . '/layout/sidebar.php'; ?>
    <main>
        <?php include __DIR__ . '/layout/topbar.php'; ?>
        <div class="content">
            <?php if (!empty($message)): ?>
                <div class="alert"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php include __DIR__ . '/' . $view . '.php'; ?>
        </div>
    </main>
<?php endif; ?>
<script src="assets/app.js"></script>
</body>
</html>
