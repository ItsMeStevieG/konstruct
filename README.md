# Konstruct

[![Latest Version on Packagist](https://img.shields.io/packagist/v/itsmestevieg/konstruct.svg?style=flat-square)](https://packagist.org/packages/itsmestevieg/konstruct)
[![Total Downloads](https://img.shields.io/packagist/dt/itsmestevieg/konstruct.svg?style=flat-square)](https://packagist.org/packages/itsmestevieg/konstruct)
[![PHP Version Require](https://img.shields.io/packagist/php-v/itsmestevieg/konstruct.svg?style=flat-square)](https://packagist.org/packages/itsmestevieg/konstruct)

A universal PHP configuration framework with automatic environment detection and path resolution. Perfect for projects that need to work seamlessly across development, staging, and production environments.

## Features

- 🚀 **Zero Configuration**: Works out of the box with smart defaults
- 🎯 **Environment Smart**: Automatically detects development, staging, and production
- 📁 **Path Flexible**: Handles any folder structure or domain setup
- 🔄 **Project Agnostic**: Same package works for any project type
- 🔙 **Legacy Compatible**: Works with existing projects without breaking changes
- 🧪 **Well Tested**: Comprehensive test suite included

## Installation

Install via Composer:

```bash
composer require itsmestevieg/konstruct
```

## Quick Start

### 1. Basic Usage

```php
<?php
require_once 'vendor/autoload.php';

// Initialize Konstruct
$konstruct = \Konstruct\Konstruct::getInstance();

// Now you have access to:
echo BASE_URL;        // Automatically detected base URL
echo env();           // Current environment (development/staging/production)
echo config('global.project_version'); // Configuration values
```

### 2. Using Helper Functions

```php
<?php
require_once 'vendor/autoload.php';

// Initialize once
\Konstruct\Konstruct::getInstance();

// Use helper functions anywhere
echo url('about');              // Generate URLs
echo asset('style.css', 'css'); // Asset URLs
echo is_prod() ? 'Live' : 'Dev'; // Environment checks
```

## Configuration

Create a `config.php` file in your project root:

```php
<?php
return [
    'global' => [
        'site_mode' => 'live',
        'project_version' => '1.0.0',
        'timezone' => 'Australia/Sydney',
        'debug' => false
    ],
    'environments' => [
        'development' => [
            'domain' => 'localhost',
            'protocol' => 'http',
            'subfolder' => '/myproject/',
            'database' => [
                'host' => 'localhost',
                'user' => 'root',
                'pass' => '',
                'name' => 'myproject_dev'
            ]
        ],
        'staging' => [
            'domain' => 'staging.mysite.com',
            'protocol' => 'https',
            'subfolder' => '/app/',
            'database' => [
                'host' => 'staging-db',
                'user' => 'staging_user',
                'pass' => 'staging_pass',
                'name' => 'myproject_staging'
            ]
        ],
        'production' => [
            'domain' => 'www.mysite.com',
            'protocol' => 'https',
            'subfolder' => '/',
            'database' => [
                'host' => 'prod-server',
                'user' => 'prod_user',
                'pass' => 'secure_password',
                'name' => 'myproject_prod'
            ]
        ]
    ]
];
```

## Environment Detection

Konstruct automatically detects your environment based on:

- **Domain name matching**
- **URL path analysis**
- **Protocol detection** (HTTP/HTTPS)
- **Server characteristics**
- **Custom detection rules**

### Custom Detection Rules

Fine-tune environment detection with custom rules:

```php
'detection_rules' => [
    [
        'type' => 'path',
        'condition' => 'starts_with',
        'value' => '/staging/',
        'score' => 20
    ],
    [
        'type' => 'env_var',
        'condition' => 'env_var',
        'value' => 'APP_ENV',
        'score' => 50
    ]
]
```

## Available Constants

After initialization, these constants are automatically defined:

### Environment Constants

- `KONSTRUCT_ENV` - Current environment name
- `IS_DEVELOPMENT` - Boolean for development environment
- `IS_STAGING` - Boolean for staging environment
- `IS_PRODUCTION` - Boolean for production environment

### Path Constants

- `BASE_URL` - Base URL for your application
- `PROJECT_ROOT` - File system path to project root
- `INCLUDES_PATH` - Path to includes directory
- `TEMPLATES_PATH` - Path to templates directory
- `LOGS_PATH` - Path to logs directory

### Database Constants (if configured)

- `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PREFIX`

## Helper Functions

### Environment Helpers

```php
env()              // Get current environment name
env('production')  // Check if in production
is_dev()          // Check if in development
is_staging()      // Check if in staging
is_prod()         // Check if in production
```

### Configuration Helpers

```php
config()                    // Get full configuration
config('global.timezone')   // Get specific config value
config('missing.key', 'default') // With default value
```

### URL Helpers

```php
url('about')              // Generate URL: /about
url('contact', false)     // Relative URL: /contact
asset('style.css', 'css') // CSS asset URL
asset('logo.png', 'images') // Image asset URL
canonical_url()           // Canonical URL for current page
```

### Utility Helpers

```php
project_root()    // Get project root path
force_https()     // Force HTTPS redirect
```

## Advanced Usage

### Custom Project Root

```php
// Specify custom project root
$konstruct = \Konstruct\Konstruct::getInstance('/path/to/project');
```

### Environment Override

```php
// Override environment detection
$_ENV['KONSTRUCT_ENV'] = 'staging';
$konstruct = \Konstruct\Konstruct::getInstance();
```

### Path Resolution

```php
$pathResolver = $konstruct->getPathResolver();

// Generate various URLs
echo $pathResolver->asset('app.js', 'js');
echo $pathResolver->getCanonicalUrl('/about');
echo $pathResolver->getCurrentPath();

// Security check
if ($pathResolver->isWithinProject($somePath)) {
    // Safe to access
}
```

## Legacy Project Migration

Konstruct is designed to work with existing projects:

### From Custom Config Systems

```php
// Your old system
require_once 'old-config.php';

// Add Konstruct alongside
$konstruct = \Konstruct\Konstruct::getInstance();

// Gradually migrate to Konstruct constants
// OLD: $config['database']['host']
// NEW: DB_HOST
```

### Maintaining Backward Compatibility

Konstruct automatically creates legacy constants:

- `LOC` - Numeric environment identifier (0=dev, 1=staging, 2=prod)
- `URL_CSS`, `URL_JS`, `URL_IMAGES` - Asset URL constants
- `BASE_LOC`, `LOC_INCLUDES` - Legacy path constants

## Testing

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

## Code Quality

Check code style:

```bash
composer cs-check
```

Fix code style:

```bash
composer cs-fix
```

Run static analysis:

```bash
composer analyse
```

Run all quality checks:

```bash
composer quality
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please report them using the issue tracker.

## Credits

- [Stevie G](https://github.com/itsmestevieg)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
