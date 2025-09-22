# Changelog

All notable changes to `konstruct` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2024-01-XX

### Added
- Initial release of Konstruct framework
- Automatic environment detection based on domain, path, and server characteristics
- Smart path resolution for different hosting scenarios
- PSR-4 autoloading with proper namespace structure
- Comprehensive configuration management with legacy format support
- Helper functions for easy access to framework features
- Constants for environment, paths, and database configuration
- Legacy compatibility for existing projects
- Custom detection rules for fine-tuned environment detection
- Canonical URL generation and HTTPS enforcement
- Error handling and logging with environment-specific settings
- PHPUnit test suite with comprehensive coverage
- Composer package structure with proper dependencies

### Features
- **Zero Configuration**: Works out of the box with smart defaults
- **Environment Smart**: Automatically detects development, staging, and production
- **Path Flexible**: Handles any folder structure or domain setup
- **Project Agnostic**: Same package works for any project type
- **Legacy Compatible**: Works with existing projects without breaking changes

### Environment Detection
- Domain name matching with wildcard support
- URL path analysis and subfolder detection
- Protocol detection (HTTP/HTTPS)
- Server characteristic analysis
- Environment variable override support
- Custom detection rules with scoring system

### Path Resolution
- Dynamic base URL generation
- Asset URL management (CSS, JS, images, uploads, etc.)
- File system path resolution
- Canonical URL generation
- Security checks for path traversal
- HTTPS enforcement capabilities

### Configuration Management
- Multiple configuration file format support
- Legacy configuration format conversion
- Dot notation for configuration access
- Configuration validation and merging
- Environment-specific database settings

### Helper Functions
- `konstruct()` - Get framework instance
- `env()` - Environment detection and checking
- `config()` - Configuration value access
- `url()` - URL generation
- `asset()` - Asset URL generation
- `is_dev()`, `is_staging()`, `is_prod()` - Environment checks
- `canonical_url()` - Canonical URL generation
- `force_https()` - HTTPS enforcement
- `project_root()` - Project root path access

### Constants
- Environment constants: `KONSTRUCT_ENV`, `IS_DEVELOPMENT`, `IS_STAGING`, `IS_PRODUCTION`
- Path constants: `BASE_URL`, `PROJECT_ROOT`, `INCLUDES_PATH`, `TEMPLATES_PATH`, `LOGS_PATH`
- Asset URL constants: `URL_CSS`, `URL_JS`, `URL_IMAGES`, `URL_UPLOADS`, etc.
- Database constants: `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PREFIX`
- Legacy compatibility constants: `LOC`, `BASE_LOC`, `LOC_INCLUDES`, etc.

### Requirements
- PHP 7.4 or higher
- Composer for installation

### Development Tools
- PHPUnit for testing
- PHP_CodeSniffer for code style
- PHPStan for static analysis
- Comprehensive test coverage
- GitHub Actions CI/CD ready
