<?php

declare(strict_types=1);

namespace Konstruct\Environment;

/**
 * Environment Detector - Smart environment detection
 * 
 * Automatically detects the current environment based on various factors:
 * - Domain names
 * - Server paths
 * - Environment variables
 * - Custom detection rules
 */
class EnvironmentDetector
{
    private array $detectionRules = [];

    /**
     * Detect current environment
     */
    public function detect(array $config): string
    {
        $environments = $config['environments'] ?? [];
        
        if (empty($environments)) {
            return 'development'; // Safe fallback
        }

        // Check for explicit environment variable
        $envVar = $_ENV['KONSTRUCT_ENV'] ?? $_SERVER['KONSTRUCT_ENV'] ?? null;
        if ($envVar && isset($environments[$envVar])) {
            return $envVar;
        }

        // Get current request information
        $currentContext = $this->getCurrentContext();

        // Score each environment based on how well it matches
        $scores = [];
        
        foreach ($environments as $envName => $envConfig) {
            $score = $this->calculateEnvironmentScore($envConfig, $currentContext);
            $scores[$envName] = $score;
        }

        // Return environment with highest score
        arsort($scores);
        $bestMatch = array_key_first($scores);
        
        return $bestMatch ?: 'development';
    }

    /**
     * Get current request context
     */
    private function getCurrentContext(): array
    {
        return [
            'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
            'path' => $_SERVER['REQUEST_URI'] ?? '/',
            'protocol' => $this->getProtocol(),
            'server_name' => $_SERVER['SERVER_NAME'] ?? '',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '',
            'script_name' => $_SERVER['SCRIPT_NAME'] ?? '',
            'php_self' => $_SERVER['PHP_SELF'] ?? ''
        ];
    }

    /**
     * Calculate how well an environment config matches current request
     */
    private function calculateEnvironmentScore(array $envConfig, array $current): float
    {
        $score = 0;
        $maxScore = 0;

        // Domain matching (highest priority)
        $maxScore += 100;
        if (isset($envConfig['domain'])) {
            if ($envConfig['domain'] === $current['domain']) {
                $score += 100; // Exact match
            } elseif ($this->domainMatches($envConfig['domain'], $current['domain'])) {
                $score += 80; // Pattern match
            }
        } else {
            $score += 50; // No domain specified, partial credit
        }

        // Path/subfolder matching
        $maxScore += 50;
        if (isset($envConfig['subfolder'])) {
            $configPath = rtrim($envConfig['subfolder'], '/');
            $currentPathBase = $this->extractBasePath($current['path']);
            
            if ($configPath === $currentPathBase) {
                $score += 50; // Exact path match
            } elseif ($configPath === '/' && $currentPathBase === '') {
                $score += 50; // Root path match
            } elseif (str_starts_with($current['path'], $configPath)) {
                $score += 30; // Path prefix match
            }
        } else {
            $score += 25; // No path specified, partial credit
        }

        // Protocol matching
        $maxScore += 20;
        if (isset($envConfig['protocol'])) {
            if ($envConfig['protocol'] === $current['protocol']) {
                $score += 20;
            }
        } else {
            $score += 10; // No protocol specified, partial credit
        }

        // Server-specific detection
        $maxScore += 30;
        $score += $this->detectServerEnvironment($envConfig, $current);

        // Custom detection rules
        if (isset($envConfig['detection_rules'])) {
            $customScore = $this->applyCustomRules($envConfig['detection_rules'], $current);
            $score += $customScore;
            $maxScore += 50;
        }

        // Return percentage score
        return $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
    }

    /**
     * Check if domain matches (supports wildcards)
     */
    private function domainMatches(string $configDomain, string $currentDomain): bool
    {
        // Support wildcard subdomains
        if (str_starts_with($configDomain, '*.')) {
            $baseDomain = substr($configDomain, 2);
            return str_ends_with($currentDomain, $baseDomain);
        }

        // Support regex patterns
        if (str_starts_with($configDomain, '/') && strrpos($configDomain, '/') > 0) {
            return (bool) preg_match($configDomain, $currentDomain);
        }

        return false;
    }

    /**
     * Extract base path from full request URI
     */
    private function extractBasePath(string $fullPath): string
    {
        $path = parse_url($fullPath, PHP_URL_PATH) ?: '/';
        
        // Find the base path (everything before the first dynamic part)
        $segments = explode('/', trim($path, '/'));
        
        // Common patterns that indicate we've reached dynamic content
        $dynamicIndicators = ['index.php', 'public', 'api', 'admin'];
        
        $baseParts = [];
        foreach ($segments as $segment) {
            if (in_array($segment, $dynamicIndicators)) {
                break;
            }
            if (!empty($segment)) {
                $baseParts[] = $segment;
            }
        }

        return empty($baseParts) ? '' : '/' . implode('/', $baseParts);
    }

    /**
     * Detect environment based on server characteristics
     */
    private function detectServerEnvironment(array $envConfig, array $current): float
    {
        $score = 0;

        // Check for localhost indicators
        if (in_array($current['domain'], ['localhost', '127.0.0.1', '::1'])) {
            if (($envConfig['environment'] ?? '') === 'development') {
                $score += 30;
            }
        }

        // Check for staging indicators
        $stagingIndicators = ['staging', 'test', 'dev', 'beta'];
        foreach ($stagingIndicators as $indicator) {
            if (str_contains($current['domain'], $indicator)) {
                if (($envConfig['environment'] ?? '') === 'staging') {
                    $score += 25;
                }
                break;
            }
        }

        // Check for production indicators
        if ($current['protocol'] === 'https' && 
            !in_array($current['domain'], ['localhost', '127.0.0.1']) &&
            !$this->containsStagingIndicators($current['domain'])) {
            if (($envConfig['environment'] ?? '') === 'production') {
                $score += 20;
            }
        }

        return $score;
    }

    /**
     * Check if domain contains staging indicators
     */
    private function containsStagingIndicators(string $domain): bool
    {
        $indicators = ['staging', 'test', 'dev', 'beta', 'demo'];
        foreach ($indicators as $indicator) {
            if (str_contains($domain, $indicator)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Apply custom detection rules
     */
    private function applyCustomRules(array $rules, array $current): float
    {
        $score = 0;

        foreach ($rules as $rule) {
            if ($this->evaluateRule($rule, $current)) {
                $score += $rule['score'] ?? 10;
            }
        }

        return min($score, 50); // Cap custom rule score
    }

    /**
     * Evaluate a single detection rule
     */
    private function evaluateRule(array $rule, array $current): bool
    {
        $type = $rule['type'] ?? 'domain';
        $condition = $rule['condition'] ?? 'equals';
        $value = $rule['value'] ?? '';

        $currentValue = $current[$type] ?? '';

        return match ($condition) {
            'equals' => $currentValue === $value,
            'contains' => str_contains($currentValue, $value),
            'starts_with' => str_starts_with($currentValue, $value),
            'ends_with' => str_ends_with($currentValue, $value),
            'regex' => (bool) preg_match($value, $currentValue),
            'env_var' => ($_ENV[$value] ?? $_SERVER[$value] ?? null) !== null,
            default => false
        };
    }

    /**
     * Get current protocol
     */
    private function getProtocol(): string
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
     * Add custom detection rule
     */
    public function addDetectionRule(array $rule): void
    {
        $this->detectionRules[] = $rule;
    }
}
