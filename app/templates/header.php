<?php
// Include the template helper
require_once __DIR__ . '/../helpers/TemplateHelper.php';

// Get the page title from the calling script, default to "Local Greeting"
$pageTitle = $pageTitle ?? "Local Greeting";
$currentPage = $currentPage ?? "home";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo TemplateHelper::escape($pageTitle); ?></title>
    <link rel="stylesheet" href="<?php echo TemplateHelper::asset('css/style.css'); ?>">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo TemplateHelper::escape($css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (isset($inlineCSS)): ?>
        <style><?php echo $inlineCSS; ?></style>
    <?php endif; ?>
</head>
<body>
    <header>
        <div class="container">
            <div class="logo"><a href="<?php echo TemplateHelper::url(); ?>">Local Greeting</a></div>
            <button class="hamburger-menu" aria-label="Toggle Navigation">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo TemplateHelper::url(); ?>" class="<?php echo TemplateHelper::activeClass('home'); ?>">Home</a></li>
                    <li><a href="<?php echo TemplateHelper::url('events'); ?>" class="<?php echo TemplateHelper::activeClass('events'); ?>">Events</a></li>
                </ul>
                <?php if (TemplateHelper::isLoggedIn()): ?>
                    <a href="<?php echo TemplateHelper::url('account'); ?>" class="btn btn-primary profile-link">Profile</a>
                <?php else: ?>
                    <a href="<?php echo TemplateHelper::url('login'); ?>" class="btn btn-primary profile-link">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
</body>
</html> 