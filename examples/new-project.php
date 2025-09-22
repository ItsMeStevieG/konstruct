<?php

/**
 * New Project Setup Example
 * 
 * This shows how to set up Konstruct in a brand new PHP project.
 */

// 1. Install Konstruct via Composer
// composer require itsmestevieg/konstruct

// 2. Create your project structure:
/*
myproject/
├── vendor/                 # Composer dependencies
├── public/                 # Web-accessible files
│   ├── index.php          # Main entry point
│   ├── css/
│   ├── js/
│   ├── images/
│   └── uploads/
├── includes/               # Private files
│   ├── templates/         # Template files
│   └── classes/           # PHP classes
├── logs/                  # Log files
├── config.php             # Konstruct configuration
└── composer.json          # Composer configuration
*/

// 3. Create config.php in your project root:
$config = [
    'global' => [
        'site_mode' => 'live',
        'project_version' => '1.0.0',
        'timezone' => 'America/New_York',
        'theme' => 'default',
        'database_enabled' => true,
        'debug' => false
    ],
    'environments' => [
        'development' => [
            'environment' => 'development',
            'domain' => 'localhost',
            'protocol' => 'http',
            'subfolder' => '/myproject/',
            'database' => [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'name' => 'myproject_dev',
                'prefix' => 'dev_'
            ],
            'debug' => true
        ],
        'staging' => [
            'environment' => 'staging',
            'domain' => 'staging.myproject.com',
            'protocol' => 'https',
            'subfolder' => '/',
            'database' => [
                'host' => 'staging-db.example.com',
                'user' => 'staging_user',
                'pass' => 'staging_password',
                'name' => 'myproject_staging',
                'prefix' => 'stg_'
            ]
        ],
        'production' => [
            'environment' => 'production',
            'domain' => 'www.myproject.com',
            'protocol' => 'https',
            'subfolder' => '/',
            'database' => [
                'host' => 'prod-db.example.com',
                'user' => 'prod_user',
                'pass' => 'secure_prod_password',
                'name' => 'myproject_prod',
                'prefix' => ''
            ],
            'force_https' => true
        ]
    ]
];

// Save this as config.php:
// file_put_contents('config.php', "<?php\nreturn " . var_export($config, true) . ";\n");

// 4. Create public/index.php (your main entry point):
/*
<?php
require_once '../vendor/autoload.php';

// Initialize Konstruct
\Konstruct\Konstruct::getInstance();

// Now you have access to all constants and helpers:
// BASE_URL, PROJECT_ROOT, DB_HOST, etc.
// env(), is_dev(), config(), url(), asset(), etc.

// Example routing
$page = $_GET['page'] ?? 'home';

// Validate page
$allowedPages = ['home', 'about', 'contact', 'services'];
if (!in_array($page, $allowedPages)) {
    $page = '404';
}

// Page data
$pageData = [
    'title' => ucfirst($page),
    'canonical_url' => canonical_url(),
    'base_url' => BASE_URL,
    'assets_url' => ASSETS_URL,
    'current_year' => date('Y'),
    'environment' => env(),
    'is_dev' => is_dev()
];

// Include page template
$templateFile = TEMPLATES_PATH . "pages/{$page}.php";
if (file_exists($templateFile)) {
    include $templateFile;
} else {
    include TEMPLATES_PATH . 'pages/404.php';
}
*/

// 5. Create .htaccess for clean URLs:
/*
RewriteEngine On

# Development .htaccess (save as .htaccess.development)
RewriteBase /myproject/

# Static files
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Clean URLs
RewriteRule ^$ public/index.php [L]
RewriteRule ^([^/]+)/?$ public/index.php?page=$1 [L,QSA]

# Production .htaccess (save as .htaccess)
RewriteBase /

# Static files
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Clean URLs
RewriteRule ^$ public/index.php [L]
RewriteRule ^([^/]+)/?$ public/index.php?page=$1 [L,QSA]
*/

// 6. Example template file (includes/templates/pages/home.php):
/*
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageData['title']) ?> - My Project</title>
    <link rel="canonical" href="<?= htmlspecialchars($pageData['canonical_url']) ?>">
    <link rel="stylesheet" href="<?= asset('style.css', 'css') ?>">
</head>
<body>
    <header>
        <nav>
            <a href="<?= url() ?>">Home</a>
            <a href="<?= url('about') ?>">About</a>
            <a href="<?= url('contact') ?>">Contact</a>
        </nav>
    </header>
    
    <main>
        <h1>Welcome to My Project</h1>
        <p>Current environment: <?= env() ?></p>
        <?php if (is_dev()): ?>
            <div class="debug">
                <h3>Debug Information</h3>
                <p>Base URL: <?= BASE_URL ?></p>
                <p>Project Root: <?= PROJECT_ROOT ?></p>
            </div>
        <?php endif; ?>
    </main>
    
    <footer>
        <p>&copy; <?= $pageData['current_year'] ?> My Project</p>
    </footer>
    
    <script src="<?= asset('app.js', 'js') ?>"></script>
</body>
</html>
*/

// 7. Database setup example:
/*
// In your initialization code
if (config('global.database_enabled')) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        
        // Make database globally available
        $GLOBALS['db'] = $pdo;
        
    } catch (PDOException $e) {
        if (is_dev()) {
            die('Database connection failed: ' . $e->getMessage());
        } else {
            error_log('Database connection failed: ' . $e->getMessage());
            die('Database connection failed. Please try again later.');
        }
    }
}

// Helper function to get database
function db() {
    return $GLOBALS['db'] ?? null;
}
*/

// 8. Environment-specific behavior:
/*
// Force HTTPS in production
if (is_prod()) {
    force_https();
}

// Enable error display in development
if (is_dev()) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Set up caching based on environment
$cacheEnabled = !is_dev();
$cacheDir = PROJECT_ROOT . '/cache/';

if ($cacheEnabled && !is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}
*/

echo "New project setup example complete!\n";
echo "Follow the comments above to set up your new project with Konstruct.\n";
