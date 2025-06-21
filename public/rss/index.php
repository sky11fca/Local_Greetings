<?php
header('Content-Type: text/html; charset=utf-8');
$rss_dir = __DIR__;
$files = glob("*.xml");

if (empty($files)) {
    die("<h1>No RSS feeds found</h1>");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generated RSS Feeds</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        ul { list-style-type: none; padding: 0; }
        li { margin: 10px 0; padding: 10px; background: #f5f5f5; border-radius: 4px; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
<h1>Available RSS Feeds</h1>
<ul>
    <?php foreach ($files as $file): ?>
        <?php
        $event_id = str_replace(['event_', '.rss'], '', $file);
        $file_url = "http://localhost/local_greeter/public/rss/$file";
        ?>
        <li>
            <strong>Event ID:</strong> <?= htmlspecialchars($event_id) ?><br>
            <a href="<?= htmlspecialchars($file_url) ?>" target="_blank">
                <?= htmlspecialchars($file) ?>
            </a>
            <small>(<?= date("Y-m-d H:i:s", filemtime($file)) ?>)</small>
        </li>
    <?php endforeach; ?>
</ul>
</body>
</html>