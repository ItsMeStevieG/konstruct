<?php

declare(strict_types=1);

/**
 * Konstruct Helper Functions
 * 
 * Global helper functions for easy access to Konstruct functionality
 */

use Konstruct\Konstruct;

if (!function_exists('konstruct')) {
    /**
     * Get Konstruct instance
     */
    function konstruct(): Konstruct
    {
        return Konstruct::getInstance();
    }
}

if (!function_exists('env')) {
    /**
     * Get environment name or check if in specific environment
     */
    function env(?string $check = null): string|bool
    {
        $konstruct = konstruct();
        if ($check === null) {
            return $konstruct->getEnvironment();
        }
        return $konstruct->isEnvironment($check);
    }
}

if (!function_exists('config')) {
    /**
     * Get configuration value
     */
    function config(?string $key = null, $default = null)
    {
        $konstruct = konstruct();
        if ($key === null) {
            return $konstruct->getConfig();
        }
        return $konstruct->get($key, $default);
    }
}

if (!function_exists('url')) {
    /**
     * Generate URL
     */
    function url(string $path = '', bool $absolute = true): string
    {
        if ($absolute) {
            return BASE_URL . ltrim($path, '/');
        }
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Generate asset URL
     */
    function asset(string $path, string $type = 'assets'): string
    {
        return konstruct()->getPathResolver()->asset($path, $type);
    }
}

if (!function_exists('is_dev')) {
    /**
     * Check if in development environment
     */
    function is_dev(): bool
    {
        return defined('IS_DEVELOPMENT') && IS_DEVELOPMENT;
    }
}

if (!function_exists('is_staging')) {
    /**
     * Check if in staging environment
     */
    function is_staging(): bool
    {
        return defined('IS_STAGING') && IS_STAGING;
    }
}

if (!function_exists('is_prod')) {
    /**
     * Check if in production environment
     */
    function is_prod(): bool
    {
        return defined('IS_PRODUCTION') && IS_PRODUCTION;
    }
}

if (!function_exists('canonical_url')) {
    /**
     * Generate canonical URL for current page
     */
    function canonical_url(?string $path = null): string
    {
        return konstruct()->getPathResolver()->getCanonicalUrl($path);
    }
}

if (!function_exists('force_https')) {
    /**
     * Force HTTPS redirect if not already HTTPS
     */
    function force_https(): void
    {
        konstruct()->getPathResolver()->forceHttps();
    }
}

if (!function_exists('project_root')) {
    /**
     * Get project root path
     */
    function project_root(): string
    {
        return konstruct()->getProjectRoot();
    }
}
