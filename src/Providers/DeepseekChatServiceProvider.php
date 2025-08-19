<?php

namespace Filaforge\DeepseekChat\Providers;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DeepseekChatServiceProvider extends PackageServiceProvider
{
    public static string $name = 'deepseek-chat';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile('deepseek-chat')
            ->hasViews()
            ->hasTranslations()
            ->hasMigrations()
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations();
            });
    }

    public function packageBooted(): void
    {
        // Register the CSS asset
        FilamentAsset::register([
            Css::make('deepseek-chat', __DIR__ . '/../../resources/css/deepseek-chat.css'),
        ], package: 'filaforge/deepseek-chat');

        // Auto-run migrations and publish assets if they haven't been run yet
        $this->autoSetup();
    }

    protected function autoSetup(): void
    {
        // Check if we're in a console command or web request
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            // Skip auto-setup during console commands to avoid conflicts
            return;
        }

        // Check if migrations table exists (Laravel is ready)
        if (!Schema::hasTable('migrations')) {
            return;
        }

        // Check if this is a first-time installation
        $isFirstInstall = $this->isFirstTimeInstallation();

        // Check if our migrations have already been run
        $migrationFiles = [
            '2025_08_12_000000_add_deepseek_settings_to_users_table',
            '2025_08_12_000001_create_deepseek_conversations_table'
        ];

        $migrationsRun = false;
        foreach ($migrationFiles as $migrationFile) {
            $migrationPath = __DIR__ . '/../../database/migrations/' . $migrationFile . '.php';
            if (file_exists($migrationPath)) {
                // Check if this migration has been run
                $migrationName = str_replace('.php', '', $migrationFile);
                if (!$this->hasMigrationBeenRun($migrationName)) {
                    // Run the migration
                    $this->runMigration($migrationPath);
                    $migrationsRun = true;
                }
            }
        }

        // Publish assets, views, and config if needed
        $assetsPublished = $this->publishAssets();

        // Run optimize if migrations were run or assets were published
        if ($migrationsRun || $assetsPublished || $this->shouldRunOptimize()) {
            $this->runOptimize();
        }

        // Log installation completion
        if ($isFirstInstall && ($migrationsRun || $assetsPublished)) {
            Log::info('DeepSeek Chat plugin installation completed successfully');
        }
    }

    protected function isFirstTimeInstallation(): bool
    {
        // Check if any of our assets exist in the application
        $configExists = file_exists(config_path('deepseek-chat.php'));
        $viewsExist = is_dir(resource_path('views/vendor/deepseek-chat'));
        $migrationsExist = !empty(glob(database_path('migrations') . '/*_add_deepseek_settings_to_users_table.php'));

        return !$configExists && !$viewsExist && !$migrationsExist;
    }

    protected function publishAssets(): bool
    {
        $assetsPublished = false;

        try {
            // Check if config has been published
            $configPath = config_path('deepseek-chat.php');
            if (!file_exists($configPath)) {
                $this->publishConfig();
                $assetsPublished = true;
            }

            // Check if views have been published
            $viewsPath = resource_path('views/vendor/deepseek-chat');
            if (!is_dir($viewsPath)) {
                $this->publishViews();
                $assetsPublished = true;
            }

            // Check if migrations have been published
            $migrationsPath = database_path('migrations');
            $publishedMigrations = glob($migrationsPath . '/*_add_deepseek_settings_to_users_table.php');
            if (empty($publishedMigrations)) {
                $this->publishMigrations();
                $assetsPublished = true;
            }

        } catch (\Exception $e) {
            Log::warning('Failed to publish DeepSeek Chat assets: ' . $e->getMessage());
        }

        return $assetsPublished;
    }

    protected function publishConfig(): void
    {
        try {
            $configSource = __DIR__ . '/../../config/deepseek-chat.php';
            $configDest = config_path('deepseek-chat.php');

            if (file_exists($configSource) && !file_exists($configDest)) {
                if (!is_dir(config_path())) {
                    mkdir(config_path(), 0755, true);
                }
                copy($configSource, $configDest);
                Log::info('DeepSeek Chat config published successfully');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to publish DeepSeek Chat config: ' . $e->getMessage());
        }
    }

    protected function publishViews(): void
    {
        try {
            $viewsSource = __DIR__ . '/../../resources/views';
            $viewsDest = resource_path('views/vendor/deepseek-chat');

            if (is_dir($viewsSource) && !is_dir($viewsDest)) {
                if (!is_dir(resource_path('views/vendor'))) {
                    mkdir(resource_path('views/vendor'), 0755, true);
                }
                $this->copyDirectory($viewsSource, $viewsDest);
                Log::info('DeepSeek Chat views published successfully');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to publish DeepSeek Chat views: ' . $e->getMessage());
        }
    }

    protected function publishMigrations(): void
    {
        try {
            $migrationsSource = __DIR__ . '/../../database/migrations';
            $migrationsDest = database_path('migrations');

            if (is_dir($migrationsSource)) {
                $files = glob($migrationsSource . '/*.php');
                foreach ($files as $file) {
                    $filename = basename($file);
                    $destFile = $migrationsDest . '/' . $filename;

                    if (!file_exists($destFile)) {
                        copy($file, $destFile);
                    }
                }
                Log::info('DeepSeek Chat migrations published successfully');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to publish DeepSeek Chat migrations: ' . $e->getMessage());
        }
    }

    protected function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = opendir($source);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $sourcePath = $source . '/' . $file;
            $destPath = $destination . '/' . $file;

            if (is_dir($sourcePath)) {
                $this->copyDirectory($sourcePath, $destPath);
            } else {
                copy($sourcePath, $destPath);
            }
        }
        closedir($dir);
    }

    protected function shouldRunOptimize(): bool
    {
        // Check if we should run optimize (e.g., if config was just published)
        $configPath = config_path('deepseek-chat.php');
        $configExists = file_exists($configPath);

        // If config doesn't exist, we definitely need to run optimize
        if (!$configExists) {
            return true;
        }

        // Check if views were published
        $viewsPath = resource_path('views/vendor/deepseek-chat');
        if (!is_dir($viewsPath)) {
            return true;
        }

        return false;
    }

    protected function runOptimize(): void
    {
        try {
            // Run optimize command
            Artisan::call('optimize');
            Log::info('DeepSeek Chat optimize completed successfully');
        } catch (\Exception $e) {
            Log::warning('Failed to run DeepSeek Chat optimize: ' . $e->getMessage());
        }
    }

    protected function autoRunMigrations(): void
    {
        // This method is now deprecated in favor of autoSetup
        $this->autoSetup();
    }

    protected function hasMigrationBeenRun(string $migrationName): bool
    {
        try {
            // Check if the migration exists in the migrations table
            $migrations = \DB::table('migrations')
                ->where('migration', 'like', "%{$migrationName}%")
                ->count();
            return $migrations > 0;
        } catch (\Exception $e) {
            // If we can't check, assume it hasn't been run
            Log::warning('Could not check migration status for DeepSeek Chat: ' . $e->getMessage());
            return false;
        }
    }

    protected function runMigration(string $migrationPath): void
    {
        try {
            // Include and run the migration
            require_once $migrationPath;
            $migrationClass = $this->getMigrationClassFromFile($migrationPath);
            if ($migrationClass) {
                $migration = new $migrationClass();
                $migration->up();

                // Log successful migration
                Log::info("DeepSeek Chat migration executed successfully: {$migrationClass}");
            }
        } catch (\Exception $e) {
            // Log error but don't fail the package installation
            Log::warning('Failed to auto-run DeepSeek Chat migration: ' . $e->getMessage());
        }
    }

    protected function getMigrationClassFromFile(string $migrationPath): ?string
    {
        try {
            $content = file_get_contents($migrationPath);
            if (preg_match('/class\s+(\w+)\s+extends\s+Migration/', $content, $matches)) {
                return $matches[1];
            }
        } catch (\Exception $e) {
            Log::warning('Could not read migration file: ' . $e->getMessage());
        }
        return null;
    }
}
