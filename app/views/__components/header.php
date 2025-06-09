<!DOCTYPE html>
<html>
<head>
    <title>Local Greeter</title>

    <!-- Load Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>

    <style>
        body {
            margin: 0;
            padding: 0;
        }
        #map {
            width: 50%;
            height: 50vh;
        }
        .info {
            padding: 6px 8px;
            font: 14px/16px Arial, Helvetica, sans-serif;
            background: white;
            background: rgba(255,255,255,0.8);
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            border-radius: 5px;
        }
        .info h4 {
            margin: 0 0 5px;
            color: #777;
        }
    </style>

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