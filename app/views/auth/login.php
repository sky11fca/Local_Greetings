<?php require __DIR__ . '/../__components/header.php'; ?>

<h1>LOGIN</h1>
<hr>

<?php if(isset($error)): ?>
<p><?php echo $error; ?></p>
<?php endif; ?>

<form action="?action=login" method="post">
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <button type="submit">Login</button>
</form>

<?php require __DIR__ . '/../__components/footer.php'; ?>
