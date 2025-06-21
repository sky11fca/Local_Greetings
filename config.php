<?php

// Database configuration - TEMPORARILY USING ROOT
// TODO: Fix bobby user permissions and change back
define('DB_HOST', '127.0.0.1');
define('DB_USERNAME', 'root');  // Temporarily using root
define('DB_PASSWORD', '');      // Leave empty if no root password
define('DB_NAME', 'local_greeter');

// JWT Secret Key - DO NOT CHANGE THIS VALUE
// This key is crucial for securing user sessions.
define('APP_SECRET_KEY', '4kL/QMq4iO99vYxhetbVh+uu606R+DzJu1j+yAqb5iQ=');

// Other application settings
define('APP_URL', '/local_greeter');
define('APP_ROOT', __DIR__);
