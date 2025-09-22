<?php

/**
 * Legacy Project Integration Example
 * 
 * This shows how to integrate Konstruct into your existing PHP project
 * that uses a custom configuration system.
 */

// 1. Install via Composer (run this in your project root):
// composer require itsmestevieg/konstruct

// 2. Replace your existing config loading in your main entry point:

// OLD CODE (typical legacy config loading):
/*
// Complex environment detection
if (file_exists("../../config.php")) {
    // Production config path
    require_once("../../config.php");
} elseif (file_exists("../config.php")) {
    // Development config path
    require_once("../config.php");
} else {
    // Fallback config
    require_once("config.php");
}
*/

// NEW CODE:
require_once '../vendor/autoload.php';

// Initialize Konstruct
$konstruct = \Konstruct\Konstruct::getInstance();

// Your existing code continues to work because Konstruct creates
// all the same constants and variables you're used to:
// - BASE_URL
// - DB_HOST, DB_USER, DB_PASS, DB_NAME
// - URL_CSS, URL_JS, URL_IMAGES, etc.
// - IS_DEVELOPMENT, IS_STAGING, IS_PRODUCTION

// 3. Create a new config.php in your project root:
$exampleConfig = [
    'global' => [
        'site_mode' => 'live',
        'project_version' => '1.0.0',
        'timezone' => 'UTC',
        'theme' => 'default',
        'database_enabled' => true
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
                'prefix' => ''
            ]
        ],
        'staging' => [
            'environment' => 'staging',
            'domain' => 'staging.mysite.com',
            'protocol' => 'https',
            'subfolder' => '/app/',
            'database' => [
                'host' => 'staging-db-host',
                'user' => 'staging_user',
                'pass' => 'staging_password',
                'name' => 'myproject_staging',
                'prefix' => ''
            ],
            'detection_rules' => [
                [
                    'type' => 'path',
                    'condition' => 'starts_with',
                    'value' => '/app/',
                    'score' => 25
                ]
            ]
        ],
        'production' => [
            'environment' => 'production',
            'domain' => 'www.mysite.com',
            'protocol' => 'https',
            'subfolder' => '/',
            'database' => [
                'host' => 'production-db-host',
                'user' => 'prod_user',
                'pass' => 'secure_production_password',
                'name' => 'myproject_prod',
                'prefix' => ''
            ],
            'force_https' => true,
            'detection_rules' => [
                [
                    'type' => 'path',
                    'condition' => 'equals',
                    'value' => '/',
                    'score' => 30
                ]
            ]
        ]
    ]
];

// 4. Update your initialization code to use Konstruct constants:

// OLD CODE:
/*
$dataHandler = new DataHandler(__DIR__); // Hardcoded path
*/

// NEW CODE:
/*
$dataHandler = new DataHandler(PROJECT_ROOT); // Use Konstruct constant

// Example: Twig template engine setup
$loader = new \Twig\Loader\FilesystemLoader([
    TEMPLATES_PATH, // Use Konstruct constants
    TEMPLATES_PATH . 'pages',
    TEMPLATES_PATH . 'partials',
]);

$twig = new Environment($loader, [
    'cache' => TEMPLATES_PATH . 'cache',
    'debug' => is_dev(), // Use Konstruct helper
]);

// Database connection using Konstruct constants
if (config('global.database_enabled')) {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );
}
*/

// 5. Fix canonical URL issues in your page data:

// OLD CODE:
/*
$pageData = [
    'CURRENT_URL' => BASE_URL . "$page/",
    // ...
];
*/

// NEW CODE:
/*
$pageData = [
    'CURRENT_URL' => $page === 'home' ? BASE_URL : BASE_URL . "$page/",
    // Or use the helper function:
    'CURRENT_URL' => canonical_url(),
    // ...
];
*/

// 6. Use helper functions throughout your code:

echo "Current environment: " . env() . "\n";
echo "Is production: " . (is_prod() ? 'Yes' : 'No') . "\n";
echo "Base URL: " . BASE_URL . "\n";
echo "Asset URL: " . asset('style.css', 'css') . "\n";
echo "Config value: " . config('global.project_version') . "\n";

// 7. Benefits you get immediately:

// ✅ Automatic environment detection
// ✅ No more hardcoded paths in .htaccess
// ✅ Proper canonical URLs (/ instead of /home/)
// ✅ Works across dev (/myproject/), staging (/app/), production (/)
// ✅ All your existing code continues to work
// ✅ Better error handling and logging
// ✅ Reusable across other projects

// 8. Generate environment-specific .htaccess files:
/*
// Create a simple script to generate .htaccess files:
$konstruct = \Konstruct\Konstruct::getInstance();
$config = $konstruct->getConfig();

foreach ($config['environments'] as $envName => $envConfig) {
    $subfolder = rtrim($envConfig['subfolder'] ?? '/', '/');
    $rewriteBase = $subfolder === '' ? '/' : $subfolder . '/';
    
    $htaccess = "RewriteEngine On\n";
    $htaccess .= "RewriteBase $rewriteBase\n\n";
    $htaccess .= "# Your existing rewrite rules here...\n";
    
    $filename = $envName === 'production' ? '.htaccess' : ".htaccess.$envName";
    file_put_contents($filename, $htaccess);
    echo "Generated: $filename\n";
}
*/
