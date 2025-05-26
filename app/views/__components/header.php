<!DOCTYPE html>
<html>
<head>
    <title>Local Greeter</title>
</head>
<body>
    <nav>
        <?php if(isset($_SESSION['username'])) : ?>
            <a href="?action=home">Home</a>
            <a href="?action=logout">Logout</a>
        <?php else: ?>
            <a href="?action=login">Login</a>
            <a href="?action=register">Register</a>
        <?php endif; ?>
    </nav>
    <main>