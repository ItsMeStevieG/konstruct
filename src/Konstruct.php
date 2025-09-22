<?php

declare(strict_types=1);

namespace Konstruct;

use Konstruct\Environment\EnvironmentDetector;
use Konstruct\Path\PathResolver;
use Konstruct\Config\ConfigManager;

/**
 * Konstruct Framework - Universal Configuration System
 * 
 * A reusable configuration framework that automatically detects environments,
 * resolves paths, and manages project settings across different hosting scenarios.
 * 
 * @version 1.0.0
 */
class Konstruct
{
    private static ?self $instance = null;
    private array $config = [];
    private ?string $environment = null;
    private string $projectRoot;
    private EnvironmentDetector $environmentDetector;
    private PathResolver $pathResolver;
    private ConfigManager $configManager;

    /**
     * Get Konstruct singleton instance
     */
    public static function getInstance(?string $projectRoot = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($projectRoot);
        }
        return self::$instance;
    }

    /**
     * Initialize Konstruct
     */
    private function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?: $this->detectProjectRoot();
        $this->loadDependencies();
        $this->initialize();
    }

    /**
     * Load core dependencies
     */
    private function loadDependencies(): void
    {
        $this->environmentDetector = new EnvironmentDetector();
        $this->pathResolver = new PathResolver($this->projectRoot);
        $this->configManager = new ConfigManager($this->projectRoot);
    }

    /**
     * Initialize the framework
     */
    private function initialize(): void
    {
        // Load project configuration
        $this->config = $this->configManager->loadConfig();
        
        // Detect current environment
        $this->environment = $this->environmentDetector->detect($this->config);
        
        // Set up paths and constants
        $this->setupConstants();
        
        // Configure PHP settings based on environment
        $this->configurePhp();
    }

    /**
     * Auto-detect project root directory
     */
    private function detectProjectRoot(): string
    {
        $currentDir = dirname(__DIR__, 4); // Go up from vendor/yourusername/konstruct/src
        
        // If we're not in vendor, try to find project root
        if (!str_contains($currentDir, 'vendor')) {
            $currentDir = dirname(__DIR__);
            
            // Look for common project indicators
            $indicators = ['composer.json', 'config.php', '.git', 'public', 'index.php'];
            
            // Start from current directory and go up
            $maxLevels = 5;
            for ($i = 0; $i < $maxLevels; $i++) {
                foreach ($indicators as $indicator) {
                    if (file_exists($currentDir . '/' . $indicator)) {
                        return $currentDir;
                    }
                }
                $currentDir = dirname($currentDir);
            }
        }
        
        return $currentDir;
    }

    /**
     * Set up constants and global variables
     */
    private function setupConstants(): void
    {
        $envConfig = $this->getEnvironmentConfig();
        $paths = $this->pathResolver->resolvePaths($envConfig);

        // Core constants
        if (!defined('KONSTRUCT_VERSION')) {
            define('KONSTRUCT_VERSION', '1.0.0');
        }
        if (!defined('KONSTRUCT_ENV')) {
            define('KONSTRUCT_ENV', $this->environment);
        }
        if (!defined('PROJECT_ROOT')) {
            define('PROJECT_ROOT', $this->projectRoot);
        }
        
        // Environment constants
        if (!defined('IS_DEVELOPMENT')) {
            define('IS_DEVELOPMENT', $this->environment === 'development');
        }
        if (!defined('IS_STAGING')) {
            define('IS_STAGING', $this->environment === 'staging');
        }
        if (!defined('IS_PRODUCTION')) {
            define('IS_PRODUCTION', $this->environment === 'production');
        }
        
        // Path constants
        if (!defined('BASE_URL')) {
            define('BASE_URL', $paths['base_url']);
        }
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', $paths['base_path']);
        }
        if (!defined('ASSETS_URL')) {
            define('ASSETS_URL', $paths['assets_url']);
        }
        if (!defined('UPLOADS_URL')) {
            define('UPLOADS_URL', $paths['uploads_url']);
        }
        
        // File system paths
        if (!defined('INCLUDES_PATH')) {
            define('INCLUDES_PATH', $this->projectRoot . '/includes/');
        }
        if (!defined('TEMPLATES_PATH')) {
            define('TEMPLATES_PATH', $this->projectRoot . '/includes/templates/');
        }
        if (!defined('CLASSES_PATH')) {
            define('CLASSES_PATH', $this->projectRoot . '/includes/classes/');
        }
        if (!defined('LOGS_PATH')) {
            define('LOGS_PATH', $this->projectRoot . '/logs/');
        }
        
        // Database constants (if configured)
        if (isset($envConfig['database'])) {
            if (!defined('DB_HOST')) {
                define('DB_HOST', $envConfig['database']['host']);
            }
            if (!defined('DB_USER')) {
                define('DB_USER', $envConfig['database']['user']);
            }
            if (!defined('DB_PASS')) {
                define('DB_PASS', $envConfig['database']['pass']);
            }
            if (!defined('DB_NAME')) {
                define('DB_NAME', $envConfig['database']['name']);
            }
            if (!defined('DB_PREFIX')) {
                define('DB_PREFIX', $envConfig['database']['prefix'] ?? '');
            }
        }

        // Legacy compatibility constants
        $this->setupLegacyConstants($paths);
    }

    /**
     * Set up legacy compatibility constants
     */
    private function setupLegacyConstants(array $paths): void
    {
        // Legacy URL constants for backward compatibility
        if (!defined('URL_CSS')) {
            define('URL_CSS', $paths['css_url']);
        }
        if (!defined('URL_JS')) {
            define('URL_JS', $paths['js_url']);
        }
        if (!defined('URL_IMAGES')) {
            define('URL_IMAGES', $paths['images_url']);
        }
        if (!defined('URL_UPLOADS')) {
            define('URL_UPLOADS', $paths['uploads_url']);
        }
        if (!defined('URL_DOCS')) {
            define('URL_DOCS', $paths['docs_url']);
        }
        if (!defined('URL_FONTS')) {
            define('URL_FONTS', $paths['fonts_url']);
        }
        if (!defined('URL_VIDEOS')) {
            define('URL_VIDEOS', $paths['videos_url']);
        }

        // Legacy path constants
        if (!defined('BASE_LOC')) {
            define('BASE_LOC', PROJECT_ROOT . '/');
        }
        if (!defined('LOC_INCLUDES')) {
            define('LOC_INCLUDES', INCLUDES_PATH);
        }
        if (!defined('LOC_CLASSES')) {
            define('LOC_CLASSES', CLASSES_PATH);
        }
        if (!defined('LOC_TEMPLATES')) {
            define('LOC_TEMPLATES', TEMPLATES_PATH);
        }
        if (!defined('LOC_LOGPATH')) {
            define('LOC_LOGPATH', LOGS_PATH . 'error.log');
        }

        // Legacy environment constant
        if (!defined('LOC')) {
            $envMap = ['development' => 0, 'staging' => 1, 'production' => 2];
            define('LOC', $envMap[KONSTRUCT_ENV] ?? 0);
        }
    }

    /**
     * Configure PHP settings based on environment
     */
    private function configurePhp(): void
    {
        $globalConfig = $this->config['global'] ?? [];
        
        // Set timezone
        if (isset($globalConfig['timezone'])) {
            date_default_timezone_set($globalConfig['timezone']);
        }
        
        // Configure error reporting
        switch ($this->environment) {
            case 'development':
                ini_set('display_errors', '1');
                ini_set('display_startup_errors', '1');
                ini_set('log_errors', '1');
                error_reporting(E_ALL);
                break;
                
            case 'staging':
                ini_set('display_errors', '1');
                ini_set('display_startup_errors', '1');
                ini_set('log_errors', '1');
                error_reporting(E_ALL & ~E_NOTICE);
                break;
                
            case 'production':
            default:
                ini_set('display_errors', '0');
                ini_set('display_startup_errors', '0');
                ini_set('log_errors', '1');
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
                break;
        }
        
        // Set up error logging
        $this->setupErrorHandling();
    }

    /**
     * Set up error handling and logging
     */
    private function setupErrorHandling(): void
    {
        $logFile = LOGS_PATH . 'php_errors.log';
        
        // Ensure logs directory exists
        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH, 0755, true);
        }
        
        // Custom error handler
        set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($logFile) {
            if (!(error_reporting() & $errno)) {
                return false;
            }
            
            $logMessage = "[" . date('Y-m-d H:i:s') . "] [ERROR] [$errno] $errstr in $errfile on line $errline\n";
            error_log($logMessage, 3, $logFile);
            
            // Convert critical errors to HTTP 500 in production
            if (IS_PRODUCTION && ($errno === E_USER_ERROR || $errno === E_ERROR)) {
                http_response_code(500);
                echo "Internal Server Error";
                exit();
            }
            
            return true;
        });
        
        // Handle fatal errors
        register_shutdown_function(function () use ($logFile) {
            $error = error_get_last();
            if ($error && ($error['type'] === E_ERROR || $error['type'] === E_USER_ERROR)) {
                $logMessage = "[" . date('Y-m-d H:i:s') . "] [FATAL ERROR] {$error['message']} in {$error['file']} on line {$error['line']}\n";
                error_log($logMessage, 3, $logFile);
                
                if (IS_PRODUCTION) {
                    http_response_code(500);
                    echo "Internal Server Error";
                    exit();
                }
            }
        });
    }

    // Getter methods
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getEnvironmentConfig(): array
    {
        return $this->config['environments'][$this->environment] ?? [];
    }

    public function getGlobalConfig(): array
    {
        return $this->config['global'] ?? [];
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    public function isEnvironment(string $env): bool
    {
        return $this->environment === $env;
    }

    public function getProjectRoot(): string
    {
        return $this->projectRoot;
    }

    public function getPathResolver(): PathResolver
    {
        return $this->pathResolver;
    }
}
