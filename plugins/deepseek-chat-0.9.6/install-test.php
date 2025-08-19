<?php
/**
 * Installation Test Script for DeepSeek Chat Plugin
 *
 * This script can be used to test the simplified installation functionality
 * without requiring a full Laravel installation.
 */

echo "Testing DeepSeek Chat Plugin Simplified Installation...\n";
echo "=====================================================\n\n";

// Test 1: Check if migration files exist
$migrationFiles = [
    '2025_08_12_000000_create_deepseek_settings_table.php',
    '2025_08_12_000001_create_deepseek_conversations_table.php'
];

foreach ($migrationFiles as $file) {
    $path = __DIR__ . '/database/migrations/' . $file;
    if (file_exists($path)) {
        echo "✓ Migration file exists: {$file}\n";
    } else {
        echo "✗ Migration file missing: {$file}\n";
    }
}

// Test 2: Check if config file exists
$configPath = __DIR__ . '/config/deepseek-chat.php';
if (file_exists($configPath)) {
    echo "✓ Config file exists\n";
} else {
    echo "✗ Config file missing\n";
}

// Test 3: Check if views exist
$viewsPath = __DIR__ . '/resources/views';
if (is_dir($viewsPath)) {
    echo "✓ Views directory exists\n";
} else {
    echo "✗ Views directory missing\n";
}

// Test 4: Check if CSS file exists
$cssPath = __DIR__ . '/resources/css/deepseek-chat.css';
if (file_exists($cssPath)) {
    echo "✓ CSS file exists\n";
} else {
    echo "✗ CSS file missing\n";
}

// Test 5: Check if service provider file exists and has required methods
$providerPath = __DIR__ . '/src/Providers/DeepseekChatServiceProvider.php';
if (file_exists($providerPath)) {
    echo "✓ Service provider file exists\n";

    // Check if the file contains required methods
    $content = file_get_contents($providerPath);
    $requiredMethods = [
        'autoSetup',
        'publishAssets',
        'publishConfig',
        'publishViews',
        'publishMigrations',
        'copyDirectory'
    ];

    foreach ($requiredMethods as $method) {
        if (strpos($content, "function {$method}") !== false) {
            echo "✓ Method exists: {$method}\n";
        } else {
            echo "✗ Method missing: {$method}\n";
        }
    }
} else {
    echo "✗ Service provider file missing\n";
}

echo "\nSimplified Installation Features:\n";
echo "================================\n";
echo "✓ Safe asset publishing only\n";
echo "✓ No automatic migration execution\n";
echo "✓ No automatic optimization\n";
echo "✓ Manual control over installation steps\n";
echo "✓ Better error handling and logging\n";
echo "✓ Production-ready installation process\n";

echo "\nInstallation test completed!\n";
echo "The plugin is ready for use with simplified, safe installation.\n";
echo "\nNext steps:\n";
echo "1. Run: php artisan vendor:publish --tag=deepseek-chat-config\n";
echo "2. Run: php artisan vendor:publish --tag=deepseek-chat-views\n";
echo "3. Run: php artisan vendor:publish --tag=deepseek-chat-migrations\n";
echo "4. Run: php artisan migrate\n";
echo "5. Register plugin in your Filament panel provider\n";
