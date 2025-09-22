<?php

/**
 * Basic Konstruct Usage Example
 * 
 * This demonstrates the basic usage of Konstruct in a new project.
 */

require_once '../vendor/autoload.php';

// Initialize Konstruct (this should be done once in your bootstrap/entry point)
$konstruct = \Konstruct\Konstruct::getInstance();

echo "=== Konstruct Basic Usage Example ===\n\n";

// 1. Environment Information
echo "1. Environment Information:\n";
echo "   Current Environment: " . env() . "\n";
echo "   Is Development: " . (is_dev() ? 'Yes' : 'No') . "\n";
echo "   Is Staging: " . (is_staging() ? 'Yes' : 'No') . "\n";
echo "   Is Production: " . (is_prod() ? 'Yes' : 'No') . "\n\n";

// 2. Path Information
echo "2. Path Information:\n";
echo "   Base URL: " . BASE_URL . "\n";
echo "   Project Root: " . PROJECT_ROOT . "\n";
echo "   Templates Path: " . TEMPLATES_PATH . "\n";
echo "   Logs Path: " . LOGS_PATH . "\n\n";

// 3. URL Generation
echo "3. URL Generation:\n";
echo "   Home URL: " . url() . "\n";
echo "   About URL: " . url('about') . "\n";
echo "   Contact URL: " . url('contact') . "\n";
echo "   Relative URL: " . url('services', false) . "\n\n";

// 4. Asset URLs
echo "4. Asset URLs:\n";
echo "   CSS File: " . asset('style.css', 'css') . "\n";
echo "   JS File: " . asset('app.js', 'js') . "\n";
echo "   Image: " . asset('logo.png', 'images') . "\n";
echo "   Upload: " . asset('document.pdf', 'uploads') . "\n\n";

// 5. Configuration Access
echo "5. Configuration Access:\n";
echo "   Site Mode: " . config('global.site_mode', 'unknown') . "\n";
echo "   Project Version: " . config('global.project_version', '1.0.0') . "\n";
echo "   Timezone: " . config('global.timezone', 'UTC') . "\n";
echo "   Debug Mode: " . (config('global.debug', false) ? 'Enabled' : 'Disabled') . "\n\n";

// 6. Database Information (if configured)
if (defined('DB_HOST')) {
    echo "6. Database Configuration:\n";
    echo "   Host: " . DB_HOST . "\n";
    echo "   Database: " . DB_NAME . "\n";
    echo "   User: " . DB_USER . "\n";
    echo "   Prefix: " . (defined('DB_PREFIX') ? DB_PREFIX : 'none') . "\n\n";
} else {
    echo "6. Database: Not configured\n\n";
}

// 7. Canonical URL
echo "7. Canonical URL:\n";
echo "   Current Page: " . canonical_url() . "\n";
echo "   Specific Page: " . canonical_url('/about') . "\n\n";

// 8. Environment-Specific Logic
echo "8. Environment-Specific Behavior:\n";
if (is_dev()) {
    echo "   Development mode: Showing debug information\n";
    echo "   Error reporting: Full\n";
    echo "   Caching: Disabled\n";
} elseif (is_staging()) {
    echo "   Staging mode: Limited debug information\n";
    echo "   Error reporting: Errors only\n";
    echo "   Caching: Enabled\n";
} elseif (is_prod()) {
    echo "   Production mode: No debug information\n";
    echo "   Error reporting: Minimal\n";
    echo "   Caching: Fully enabled\n";
    echo "   HTTPS: " . (konstruct()->getPathResolver()->isHttps() ? 'Enabled' : 'Disabled') . "\n";
}
echo "\n";

// 9. Legacy Constants (for backward compatibility)
echo "9. Legacy Constants:\n";
echo "   LOC (numeric env): " . (defined('LOC') ? LOC : 'undefined') . "\n";
echo "   URL_CSS: " . (defined('URL_CSS') ? URL_CSS : 'undefined') . "\n";
echo "   URL_JS: " . (defined('URL_JS') ? URL_JS : 'undefined') . "\n";
echo "   BASE_LOC: " . (defined('BASE_LOC') ? BASE_LOC : 'undefined') . "\n\n";

// 10. Advanced Usage
echo "10. Advanced Usage:\n";

// Get the full configuration
$fullConfig = config();
echo "   Total environments configured: " . count($fullConfig['environments'] ?? []) . "\n";

// Get environment-specific config
$envConfig = $konstruct->getEnvironmentConfig();
echo "   Current domain: " . ($envConfig['domain'] ?? 'unknown') . "\n";
echo "   Current protocol: " . ($envConfig['protocol'] ?? 'unknown') . "\n";
echo "   Current subfolder: " . ($envConfig['subfolder'] ?? 'unknown') . "\n";

// Path resolver instance
$pathResolver = $konstruct->getPathResolver();
echo "   Current request path: " . $pathResolver->getCurrentPath() . "\n";

echo "\n=== Example Complete ===\n";

// Example of how you might use this in a real application:
/*

// In your main index.php or bootstrap file:
require_once 'vendor/autoload.php';
\Konstruct\Konstruct::getInstance();

// Now throughout your application you can use:

// Environment checks
if (is_prod()) {
    // Production-only code
    force_https();
}

// URL generation
$navLinks = [
    'Home' => url(),
    'About' => url('about'),
    'Contact' => url('contact')
];

// Asset inclusion
echo '<link rel="stylesheet" href="' . asset('style.css', 'css') . '">';
echo '<script src="' . asset('app.js', 'js') . '"></script>';

// Database connection
if (config('global.database_enabled')) {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    } catch (PDOException $e) {
        if (is_dev()) {
            throw $e;
        } else {
            error_log('Database connection failed: ' . $e->getMessage());
        }
    }
}

// Template rendering (example with Twig)
$loader = new \Twig\Loader\FilesystemLoader(TEMPLATES_PATH);
$twig = new \Twig\Environment($loader, [
    'cache' => is_prod() ? TEMPLATES_PATH . 'cache' : false,
    'debug' => is_dev()
]);

// Add global variables to templates
$twig->addGlobal('BASE_URL', BASE_URL);
$twig->addGlobal('ASSETS_URL', ASSETS_URL);
$twig->addGlobal('IS_DEV', is_dev());

*/
