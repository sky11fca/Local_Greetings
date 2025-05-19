<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iași Sports Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />
    <style>
        #map { height: 500px; }
        .container { max-width: 1200px; margin-top: 20px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?=BASE_URL?>/home">Iași Sports Network</a>
        <div class="navbar-nav">
            <?php if(isset($_COOKIE['user_id'])): ?>
                <span class="nav-item text-light me-3">Welcome, <?= htmlspecialchars($_COOKIE['username']) ?></span>
                <a class="nav-link" href="<?=BASE_URL?>/logout">Logout</a>
            <?php else: ?>
                <a class="nav-link" href="<?=BASE_URL?>/login">Login</a>
                <a class="nav-link" href="<?=BASE_URL?>/register">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="container">