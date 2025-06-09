<?php require __DIR__ . '/../__components/header.php'; ?>

<h1>Register</h1>
<hr>

<?php if(isset($error)): ?>
    <p><?php echo $error; ?></p>
<?php endif; ?>

<form action="?action=register" method="post">
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
    </div>
    <div>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <div>
        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
    </div>
    <button type="submit">Register</button>
</form>

<?php require __DIR__ . '/../__components/footer.php'; ?>
