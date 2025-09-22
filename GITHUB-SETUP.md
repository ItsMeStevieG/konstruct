# GitHub Setup & Publishing Guide

This guide will help you set up the Konstruct package on GitHub and publish it to Packagist.

## Step 1: Create GitHub Repository

1. Go to [GitHub](https://github.com) and log in as `itsmestevieg`
2. Click "New repository"
3. Repository name: `konstruct`
4. Description: `Universal PHP configuration framework with automatic environment detection and path resolution`
5. Make it **Public** (required for free Packagist)
6. Don't initialize with README (we already have one)
7. Click "Create repository"

## Step 2: Push Code to GitHub

```bash
# Navigate to your konstruct-package folder
cd konstruct-package

# Initialize git repository
git init

# Add all files
git add .

# Make initial commit
git commit -m "Initial release of Konstruct framework v1.0.0

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
- PHPUnit test suite with comprehensive coverage"

# Add GitHub remote (replace with your actual repo URL)
git remote add origin https://github.com/itsmestevieg/konstruct.git

# Set main branch
git branch -M main

# Push to GitHub
git push -u origin main
```

## Step 3: Create Release Tag

```bash
# Create and push version tag
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

## Step 4: Submit to Packagist

1. Go to [Packagist.org](https://packagist.org)
2. Click "Sign in with GitHub"
3. Authorize Packagist to access your GitHub account
4. Click "Submit" in the top menu
5. Enter your repository URL: `https://github.com/itsmestevieg/konstruct`
6. Click "Check"
7. If validation passes, click "Submit"

## Step 5: Set Up Auto-Updates (Recommended)

1. In your GitHub repository, go to Settings → Webhooks
2. Click "Add webhook"
3. Payload URL: `https://packagist.org/api/github?username=itsmestevieg`
4. Content type: `application/json`
5. Secret: (leave empty)
6. Events: Select "Just the push event"
7. Click "Add webhook"

This will automatically update Packagist when you push new versions.

## Step 6: Test Installation

```bash
# Test in a new directory
mkdir test-konstruct
cd test-konstruct
composer init --no-interaction
composer require itsmestevieg/konstruct

# Create test file
cat > test.php << 'EOF'
<?php
require_once 'vendor/autoload.php';

$konstruct = \Konstruct\Konstruct::getInstance();

echo "Environment: " . env() . "\n";
echo "Base URL: " . BASE_URL . "\n";
echo "Is Development: " . (is_dev() ? 'Yes' : 'No') . "\n";
echo "Project Root: " . PROJECT_ROOT . "\n";
EOF

php test.php
```

## Step 7: Future Updates

When you make changes:

```bash
# Make your changes
# Update CHANGELOG.md with new version info
# Update version in composer.json if needed

# Commit changes
git add .
git commit -m "Description of changes"

# Create new version tag
git tag -a v1.0.1 -m "Release version 1.0.1"

# Push changes and tags
git push origin main
git push origin v1.0.1
```

Packagist will automatically update if you set up the webhook.

## Repository Structure

Your GitHub repository should look like this:

```
konstruct/
├── .github/
│   └── workflows/
│       └── ci.yml              # GitHub Actions (optional)
├── src/
│   ├── Konstruct.php
│   ├── Environment/
│   ├── Path/
│   ├── Config/
│   └── helpers.php
├── tests/
├── examples/
├── composer.json
├── README.md
├── CHANGELOG.md
├── LICENSE
├── phpunit.xml
└── .gitignore
```

## Optional: GitHub Actions CI

Create `.github/workflows/ci.yml`:

```yaml
name: CI

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest
    
    strategy:
      matrix:
        php-version: [7.4, 8.0, 8.1, 8.2, 8.3]
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
        extensions: mbstring, xml, ctype, json
        coverage: xdebug
    
    - name: Install dependencies
      run: composer install --prefer-dist --no-progress
    
    - name: Run tests
      run: composer test
    
    - name: Run code style check
      run: composer cs-check
    
    - name: Run static analysis
      run: composer analyse
```

## Optional: .gitignore

Create `.gitignore`:

```
/vendor/
/coverage/
/build/
.phpunit.result.cache
composer.lock
.DS_Store
Thumbs.db
*.log
```

## Package URLs

Once published, your package will be available at:

- **Packagist**: https://packagist.org/packages/itsmestevieg/konstruct
- **GitHub**: https://github.com/itsmestevieg/konstruct
- **Installation**: `composer require itsmestevieg/konstruct`

## Troubleshooting

**Packagist validation fails:**
- Check composer.json syntax
- Ensure all required fields are present
- Make sure repository is public

**Auto-updates not working:**
- Check webhook configuration
- Verify webhook is receiving push events
- Check Packagist package page for update status

**Installation issues:**
- Ensure minimum PHP version requirements are met
- Check for conflicting package names
- Verify Packagist shows the latest version
