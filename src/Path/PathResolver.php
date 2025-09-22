<?php

declare(strict_types=1);

namespace Konstruct\Path;

/**
 * Path Resolver - Dynamic path and URL resolution
 * 
 * Handles path resolution for different environments and hosting scenarios
 */
class PathResolver
{
    private string $projectRoot;

    public function __construct(string $projectRoot)
    {
        $this->projectRoot = rtrim($projectRoot, '/');
    }

    /**
     * Resolve all paths for the current environment
     */
    public function resolvePaths(array $envConfig): array
    {
        $domain = $envConfig['domain'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
        $protocol = $envConfig['protocol'] ?? $this->detectProtocol();
        $subfolder = $envConfig['subfolder'] ?? '/';
        
        // Normalize subfolder
        $subfolder = $this->normalizeSubfolder($subfolder);
        
        // Build base URL
        $baseUrl = $protocol . '://' . $domain . $subfolder;
        
        // Resolve file system paths
        $basePath = $this->resolveBasePath($envConfig);
        
        return [
            'base_url' => $baseUrl,
            'base_path' => $basePath,
            'assets_url' => $baseUrl . 'assets/',
            'css_url' => $baseUrl . 'css/',
            'js_url' => $baseUrl . 'js/',
            'images_url' => $baseUrl . 'images/',
            'uploads_url' => $baseUrl . 'uploads/',
            'docs_url' => $baseUrl . 'docs/',
            'fonts_url' => $baseUrl . 'fonts/',
            'videos_url' => $baseUrl . 'videos/',
            
            // File system paths
            'assets_path' => $this->projectRoot . '/public/assets/',
            'css_path' => $this->projectRoot . '/public/css/',
            'js_path' => $this->projectRoot . '/public/js/',
            'images_path' => $this->projectRoot . '/public/images/',
            'uploads_path' => $this->projectRoot . '/public/uploads/',
            'docs_path' => $this->projectRoot . '/public/docs/',
            'fonts_path' => $this->projectRoot . '/public/fonts/',
            'videos_path' => $this->projectRoot . '/public/videos/',
        ];
    }

    /**
     * Normalize subfolder path
     */
    private function normalizeSubfolder(string $subfolder): string
    {
        if (empty($subfolder) || $subfolder === '/') {
            return '/';
        }
        
        // Ensure starts and ends with slash
        $subfolder = '/' . trim($subfolder, '/') . '/';
        
        return $subfolder;
    }

    /**
     * Resolve base file system path
     */
    private function resolveBasePath(array $envConfig): string
    {
        // Check if custom base path is specified
        if (isset($envConfig['base_path'])) {
            return rtrim($envConfig['base_path'], '/');
        }
        
        // Auto-detect based on document root and current script location
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
        $scriptPath = $_SERVER['SCRIPT_FILENAME'] ?? __FILE__;
        
        if (!empty($documentRoot) && str_starts_with($scriptPath, $documentRoot)) {
            // Calculate relative path from document root
            $relativePath = substr(dirname($scriptPath), strlen($documentRoot));
            return $documentRoot . $relativePath;
        }
        
        // Fallback to project root
        return $this->projectRoot;
    }

    /**
     * Detect current protocol
     */
    private function detectProtocol(): string
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return 'https';
        }
        
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return 'https';
        }
        
        if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            return 'https';
        }
        
        return 'http';
    }

    /**
     * Generate URL for a specific path
     */
    public function url(string $path = '', bool $absolute = true): string
    {
        $path = ltrim($path, '/');
        
        if ($absolute) {
            return BASE_URL . $path;
        }
        
        return '/' . $path;
    }

    /**
     * Generate asset URL
     */
    public function asset(string $path, string $type = 'assets'): string
    {
        $path = ltrim($path, '/');
        
        return match ($type) {
            'css' => (defined('URL_CSS') ? URL_CSS : BASE_URL . 'css/') . $path,
            'js' => (defined('URL_JS') ? URL_JS : BASE_URL . 'js/') . $path,
            'images' => (defined('URL_IMAGES') ? URL_IMAGES : BASE_URL . 'images/') . $path,
            'uploads' => (defined('URL_UPLOADS') ? URL_UPLOADS : BASE_URL . 'uploads/') . $path,
            'docs' => (defined('URL_DOCS') ? URL_DOCS : BASE_URL . 'docs/') . $path,
            'fonts' => (defined('URL_FONTS') ? URL_FONTS : BASE_URL . 'fonts/') . $path,
            'videos' => (defined('URL_VIDEOS') ? URL_VIDEOS : BASE_URL . 'videos/') . $path,
            default => (defined('ASSETS_URL') ? ASSETS_URL : BASE_URL . 'assets/') . $path
        };
    }

    /**
     * Get file system path for a specific type
     */
    public function path(string $type = 'base'): string
    {
        return match ($type) {
            'base' => defined('BASE_PATH') ? BASE_PATH : $this->projectRoot,
            'includes' => defined('INCLUDES_PATH') ? INCLUDES_PATH : $this->projectRoot . '/includes/',
            'templates' => defined('TEMPLATES_PATH') ? TEMPLATES_PATH : $this->projectRoot . '/includes/templates/',
            'classes' => defined('CLASSES_PATH') ? CLASSES_PATH : $this->projectRoot . '/includes/classes/',
            'logs' => defined('LOGS_PATH') ? LOGS_PATH : $this->projectRoot . '/logs/',
            'uploads' => defined('UPLOADS_PATH') ? UPLOADS_PATH : $this->projectRoot . '/public/uploads/',
            default => $this->projectRoot
        };
    }

    /**
     * Check if a path is within the project root (security check)
     */
    public function isWithinProject(string $path): bool
    {
        $realPath = realpath($path);
        $realProjectRoot = realpath($this->projectRoot);
        
        if ($realPath === false || $realProjectRoot === false) {
            return false;
        }
        
        return str_starts_with($realPath, $realProjectRoot);
    }

    /**
     * Resolve relative path to absolute
     */
    public function resolveRelativePath(string $relativePath, ?string $basePath = null): string
    {
        $basePath = $basePath ?: $this->projectRoot;
        
        // Handle different path separators
        $relativePath = str_replace('\\', '/', $relativePath);
        $basePath = str_replace('\\', '/', $basePath);
        
        // Remove leading slash if present
        $relativePath = ltrim($relativePath, '/');
        
        return rtrim($basePath, '/') . '/' . $relativePath;
    }

    /**
     * Get current request path
     */
    public function getCurrentPath(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        
        // Remove base subfolder from path if present
        if (defined('BASE_URL')) {
            $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '/';
            if ($basePath !== '/' && str_starts_with($path, $basePath)) {
                $path = substr($path, strlen($basePath));
                $path = '/' . ltrim($path, '/');
            }
        }
        
        return $path;
    }

    /**
     * Generate canonical URL for current page
     */
    public function getCanonicalUrl(?string $path = null): string
    {
        if ($path === null) {
            $path = $this->getCurrentPath();
        }
        
        // Ensure trailing slash for consistency
        if ($path !== '/' && !str_ends_with($path, '/')) {
            $path .= '/';
        }
        
        return BASE_URL . ltrim($path, '/');
    }

    /**
     * Check if current request is HTTPS
     */
    public function isHttps(): bool
    {
        return $this->detectProtocol() === 'https';
    }

    /**
     * Force HTTPS redirect if not already HTTPS
     */
    public function forceHttps(): void
    {
        if (!$this->isHttps()) {
            $httpsUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $httpsUrl, true, 301);
            exit;
        }
    }
}
