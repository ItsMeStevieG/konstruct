<?php

declare(strict_types=1);

namespace Konstruct\Config;

/**
 * Configuration Manager - Handles loading and parsing of project configurations
 */
class ConfigManager
{
    private string $projectRoot;
    private array $configCache = [];

    public function __construct(string $projectRoot)
    {
        $this->projectRoot = rtrim($projectRoot, '/');
    }

    /**
     * Load project configuration
     */
    public function loadConfig(): array
    {
        // Try different config file locations and formats
        $configFiles = [
            $this->projectRoot . '/config.php',
            $this->projectRoot . '/konstruct.config.php',
            $this->projectRoot . '/config/app.php',
            $this->projectRoot . '/.konstruct.php'
        ];

        foreach ($configFiles as $configFile) {
            if (file_exists($configFile)) {
                return $this->loadPhpConfig($configFile);
            }
        }

        // If no config file found, return default configuration
        return $this->getDefaultConfig();
    }

    /**
     * Load PHP configuration file
     */
    private function loadPhpConfig(string $configFile): array
    {
        // Capture any variables that might be set in the config file
        $CONFIG = [];
        
        // Include the config file
        $result = include $configFile;
        
        // Handle different config file formats
        if (is_array($result)) {
            // Config file returns array directly
            return $this->normalizeConfig($result);
        } elseif (isset($CONFIG) && is_array($CONFIG)) {
            // Config file sets $CONFIG variable (legacy format)
            return $this->normalizeConfig($CONFIG);
        } else {
            // Try to extract configuration from global variables
            return $this->extractGlobalConfig();
        }
    }

    /**
     * Extract configuration from global variables (for legacy configs)
     */
    private function extractGlobalConfig(): array
    {
        global $CONFIG;
        
        if (isset($CONFIG) && is_array($CONFIG)) {
            return $this->normalizeConfig($CONFIG);
        }
        
        return $this->getDefaultConfig();
    }

    /**
     * Normalize configuration to standard format
     */
    private function normalizeConfig(array $config): array
    {
        // Handle legacy format conversion
        if (isset($config['CONFIG']) && is_array($config['CONFIG'])) {
            // Legacy format: $CONFIG['CONFIG'][0], $CONFIG['CONFIG'][1], etc.
            $normalized = [
                'global' => $config['GLOBAL'] ?? [],
                'environments' => []
            ];
            
            foreach ($config['CONFIG'] as $envConfig) {
                if (isset($envConfig['ENVIRONMENT'])) {
                    $envName = $envConfig['ENVIRONMENT'];
                    $normalized['environments'][$envName] = [
                        'environment' => $envName,
                        'domain' => $this->extractDomainFromBaseUrl($envConfig['BASEURL'] ?? ''),
                        'protocol' => $this->extractProtocolFromBaseUrl($envConfig['BASEURL'] ?? ''),
                        'subfolder' => $this->extractSubfolderFromBaseUrl($envConfig['BASEURL'] ?? ''),
                        'database' => [
                            'host' => $envConfig['DBHOST'] ?? '',
                            'user' => $envConfig['DBUSER'] ?? '',
                            'pass' => $envConfig['DBPASS'] ?? '',
                            'name' => $envConfig['DBNAME'] ?? '',
                            'prefix' => $envConfig['DBPREFIX'] ?? ''
                        ]
                    ];
                }
            }
            
            return $normalized;
        }
        
        // Already in new format or close to it
        return [
            'global' => $config['global'] ?? $config['GLOBAL'] ?? [],
            'environments' => $config['environments'] ?? $config['ENVIRONMENTS'] ?? []
        ];
    }

    /**
     * Extract domain from BASEURL
     */
    private function extractDomainFromBaseUrl(string $baseUrl): string
    {
        if (empty($baseUrl)) {
            return 'localhost';
        }
        
        $parsed = parse_url($baseUrl);
        return $parsed['host'] ?? 'localhost';
    }

    /**
     * Extract protocol from BASEURL
     */
    private function extractProtocolFromBaseUrl(string $baseUrl): string
    {
        if (empty($baseUrl)) {
            return 'http';
        }
        
        $parsed = parse_url($baseUrl);
        return $parsed['scheme'] ?? 'http';
    }

    /**
     * Extract subfolder from BASEURL
     */
    private function extractSubfolderFromBaseUrl(string $baseUrl): string
    {
        if (empty($baseUrl)) {
            return '/';
        }
        
        $parsed = parse_url($baseUrl);
        $path = $parsed['path'] ?? '/';
        
        return $path === '' ? '/' : $path;
    }

    /**
     * Get default configuration
     */
    private function getDefaultConfig(): array
    {
        return [
            'global' => [
                'site_mode' => 'live',
                'project_version' => '1.0.0',
                'timezone' => 'UTC',
                'theme' => 'default',
                'debug' => false
            ],
            'environments' => [
                'development' => [
                    'environment' => 'development',
                    'domain' => 'localhost',
                    'protocol' => 'http',
                    'subfolder' => '/',
                    'database' => [
                        'host' => 'localhost',
                        'user' => 'root',
                        'pass' => '',
                        'name' => 'app_dev',
                        'prefix' => ''
                    ]
                ],
                'production' => [
                    'environment' => 'production',
                    'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
                    'protocol' => 'https',
                    'subfolder' => '/',
                    'database' => [
                        'host' => 'localhost',
                        'user' => '',
                        'pass' => '',
                        'name' => '',
                        'prefix' => ''
                    ]
                ]
            ]
        ];
    }

    /**
     * Save configuration to file
     */
    public function saveConfig(array $config, ?string $filename = null): bool
    {
        $filename = $filename ?: $this->projectRoot . '/config.php';
        
        $configContent = "<?php\n";
        $configContent .= "/**\n";
        $configContent .= " * Konstruct Configuration\n";
        $configContent .= " * Generated on " . date('Y-m-d H:i:s') . "\n";
        $configContent .= " */\n\n";
        $configContent .= "return " . var_export($config, true) . ";\n";
        
        return file_put_contents($filename, $configContent) !== false;
    }

    /**
     * Validate configuration structure
     */
    public function validateConfig(array $config): array|bool
    {
        $errors = [];
        
        // Check required sections
        if (!isset($config['global'])) {
            $errors[] = "Missing 'global' configuration section";
        }
        
        if (!isset($config['environments']) || !is_array($config['environments'])) {
            $errors[] = "Missing or invalid 'environments' configuration section";
        } else {
            // Validate each environment
            foreach ($config['environments'] as $envName => $envConfig) {
                if (!isset($envConfig['domain'])) {
                    $errors[] = "Environment '$envName' missing 'domain' setting";
                }
                
                if (!isset($envConfig['protocol'])) {
                    $errors[] = "Environment '$envName' missing 'protocol' setting";
                }
            }
        }
        
        return empty($errors) ? true : $errors;
    }

    /**
     * Merge configurations (useful for extending base configs)
     */
    public function mergeConfigs(array $baseConfig, array $overrideConfig): array
    {
        return array_merge_recursive($baseConfig, $overrideConfig);
    }

    /**
     * Get configuration value with dot notation
     */
    public function get(array $config, string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    /**
     * Set configuration value with dot notation
     */
    public function set(array &$config, string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$config;
        
        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }
        
        $current = $value;
    }
}
