<?php

declare(strict_types=1);

namespace Konstruct\Tests;

use PHPUnit\Framework\TestCase;
use Konstruct\Konstruct;

class KonstructTest extends TestCase
{
    private ?Konstruct $konstruct = null;

    protected function setUp(): void
    {
        // Reset singleton for each test
        $reflection = new \ReflectionClass(Konstruct::class);
        $instance = $reflection->getProperty('instance');
        $instance->setAccessible(true);
        $instance->setValue(null, null);
        
        // Create test project root
        $testRoot = __DIR__ . '/fixtures/test-project';
        if (!is_dir($testRoot)) {
            mkdir($testRoot, 0755, true);
        }
        
        $this->konstruct = Konstruct::getInstance($testRoot);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $testRoot = __DIR__ . '/fixtures/test-project';
        if (is_dir($testRoot)) {
            $this->removeDirectory($testRoot);
        }
    }

    public function testSingletonPattern(): void
    {
        $instance1 = Konstruct::getInstance();
        $instance2 = Konstruct::getInstance();
        
        $this->assertSame($instance1, $instance2);
    }

    public function testEnvironmentDetection(): void
    {
        $environment = $this->konstruct->getEnvironment();
        
        $this->assertIsString($environment);
        $this->assertContains($environment, ['development', 'staging', 'production']);
    }

    public function testConfigurationLoading(): void
    {
        $config = $this->konstruct->getConfig();
        
        $this->assertIsArray($config);
        $this->assertArrayHasKey('global', $config);
        $this->assertArrayHasKey('environments', $config);
    }

    public function testConstantsAreDefined(): void
    {
        $this->assertTrue(defined('KONSTRUCT_VERSION'));
        $this->assertTrue(defined('KONSTRUCT_ENV'));
        $this->assertTrue(defined('PROJECT_ROOT'));
        $this->assertTrue(defined('BASE_URL'));
        $this->assertTrue(defined('IS_DEVELOPMENT'));
        $this->assertTrue(defined('IS_STAGING'));
        $this->assertTrue(defined('IS_PRODUCTION'));
    }

    public function testEnvironmentChecks(): void
    {
        $env = $this->konstruct->getEnvironment();
        
        if ($env === 'development') {
            $this->assertTrue($this->konstruct->isEnvironment('development'));
            $this->assertFalse($this->konstruct->isEnvironment('production'));
        } elseif ($env === 'production') {
            $this->assertTrue($this->konstruct->isEnvironment('production'));
            $this->assertFalse($this->konstruct->isEnvironment('development'));
        }
    }

    public function testConfigurationAccess(): void
    {
        // Test getting configuration with dot notation
        $globalConfig = $this->konstruct->get('global');
        $this->assertIsArray($globalConfig);
        
        // Test with default value
        $nonExistent = $this->konstruct->get('non.existent.key', 'default');
        $this->assertEquals('default', $nonExistent);
    }

    public function testProjectRootDetection(): void
    {
        $projectRoot = $this->konstruct->getProjectRoot();
        
        $this->assertIsString($projectRoot);
        $this->assertDirectoryExists($projectRoot);
    }

    public function testPathResolver(): void
    {
        $pathResolver = $this->konstruct->getPathResolver();
        
        $this->assertInstanceOf(\Konstruct\Path\PathResolver::class, $pathResolver);
        
        // Test URL generation
        $url = $pathResolver->url('test');
        $this->assertStringContains('test', $url);
        
        // Test asset URL generation
        $assetUrl = $pathResolver->asset('style.css', 'css');
        $this->assertStringContains('css', $assetUrl);
        $this->assertStringContains('style.css', $assetUrl);
    }

    public function testHelperFunctions(): void
    {
        // Test that helper functions are available
        $this->assertTrue(function_exists('konstruct'));
        $this->assertTrue(function_exists('env'));
        $this->assertTrue(function_exists('config'));
        $this->assertTrue(function_exists('url'));
        $this->assertTrue(function_exists('asset'));
        $this->assertTrue(function_exists('is_dev'));
        $this->assertTrue(function_exists('is_staging'));
        $this->assertTrue(function_exists('is_prod'));
    }

    public function testLegacyCompatibility(): void
    {
        // Test that legacy constants are defined
        $this->assertTrue(defined('LOC'));
        $this->assertTrue(defined('URL_CSS'));
        $this->assertTrue(defined('URL_JS'));
        $this->assertTrue(defined('URL_IMAGES'));
        $this->assertTrue(defined('BASE_LOC'));
        $this->assertTrue(defined('LOC_INCLUDES'));
    }

    /**
     * Recursively remove directory
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
