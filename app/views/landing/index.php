<?php require __DIR__ . '/../__components/header.php'; ?>

<h1>LOCAL GREETER</h1>

<hr>

<?php if(!isset($_SESSION['user_id'])): ?>
<div>
    <a href="?action=login">Login</a>
    <a href="?action=register">Register</a>
</div>
<?php endif; ?>

<?php require __DIR__ . '/../__components/footer.php'; ?>
