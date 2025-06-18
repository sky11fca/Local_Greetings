# Local Greeter - Templating System

This document explains the new templating system implemented to replace the hard-coded HTML structure.

## Overview

The application now uses a **PHP-based templating system** with the following benefits:

- ✅ **Reusable components** - Header and footer are shared across all pages
- ✅ **Clean URLs** - URLs like `/local_greeter/events` instead of `/local_greeter/app/views/events.html`
- ✅ **Dynamic content** - Easy to inject user-specific data and dynamic content
- ✅ **Better maintainability** - Changes to header/footer only need to be made once
- ✅ **Security improvements** - No direct access to HTML files
- ✅ **SEO friendly** - Proper server-side rendering

## File Structure

```
local_greeter/
├── app/
│   ├── templates/
│   │   ├── header.php          # Reusable header template
│   │   └── footer.php          # Reusable footer template
│   ├── pages/
│   │   ├── home.php            # Home page content
│   │   ├── events.php          # Events page content
│   │   ├── fields.php          # Sports fields page content
│   │   ├── login.php           # Login page content
│   │   ├── register.php        # Register page content
│   │   ├── account.php         # Account page content
│   │   ├── profile.php         # Profile edit page content
│   │   └── create-event.php    # Create event page content
│   ├── helpers/
│   │   └── TemplateHelper.php  # Utility functions for templates
│   └── router.php              # URL routing system
├── index.php                   # Main entry point with router
└── .htaccess                   # URL rewriting rules
```

## How It Works

### 1. URL Routing
- All requests go through `index.php`
- The `.htaccess` file rewrites URLs to clean formats
- The `Router` class maps URLs to page files

### 2. Template System
- Each page sets variables (title, CSS, scripts) before including templates
- `header.php` and `footer.php` are included on every page
- The `TemplateHelper` class provides utility functions

### 3. Clean URLs
- Old: `/local_greeter/app/views/events.html`
- New: `/local_greeter/events`

## Creating a New Page

1. **Create the page file** in `app/pages/`:
```php
<?php
// Set page-specific variables
$pageTitle = "My New Page";
$currentPage = "my-page";

// Additional CSS files for this page
$additionalCSS = [
    "/local_greeter/public/css/my-page.css"
];

// Additional scripts for this page
$additionalScripts = [
    "/local_greeter/public/js/my-page.js"
];

// Include the header template
include __DIR__ . '/../templates/header.php';
?>

<main>
    <!-- Your page content here -->
    <div class="container">
        <h1>My New Page</h1>
        <p>This is the content of my new page.</p>
    </div>
</main>

<?php
// Include the footer template
include __DIR__ . '/../templates/footer.php';
?>
```

2. **Add the route** in `app/router.php`:
```php
$this->routes = [
    // ... existing routes
    'my-page' => 'my-page'
];
```

## Template Helper Functions

The `TemplateHelper` class provides useful functions:

```php
// Asset URLs
TemplateHelper::asset('css/style.css')  // → /local_greeter/public/css/style.css

// Application URLs
TemplateHelper::url('events')           // → /local_greeter/events

// User authentication
TemplateHelper::isLoggedIn()            // → true/false
TemplateHelper::getUserData()           // → user data array or null

// HTML escaping
TemplateHelper::escape($string)         // → escaped HTML

// Navigation helpers
TemplateHelper::activeClass('home')     // → 'active' or ''
```

## Benefits Over the Old System

| Aspect | Old System | New System |
|--------|------------|------------|
| **Maintenance** | Update every file for header changes | Update once in header.php |
| **URLs** | Hard-coded absolute paths | Clean, SEO-friendly URLs |
| **Security** | Direct HTML file access | No direct file access |
| **Dynamic Content** | Static HTML only | PHP-powered dynamic content |
| **Code Reuse** | Duplicated header/footer | Shared templates |
| **Scalability** | Adding pages requires full HTML | Simple PHP files |

## Migration Notes

- All existing functionality is preserved
- JavaScript files remain unchanged
- CSS files remain unchanged
- API endpoints remain unchanged
- Only the page structure has been improved

## Testing

To test the new system:

1. Visit `/local_greeter/` - Should show the home page
2. Visit `/local_greeter/events` - Should show the events page
3. Visit `/local_greeter/login` - Should show the login page
4. All navigation links should work with clean URLs

The system maintains backward compatibility while providing a much better foundation for future development. 