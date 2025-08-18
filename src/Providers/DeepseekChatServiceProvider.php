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
        // Only run during web requests, not console commands
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        // Check if Laravel is ready (migrations table exists)
        if (!Schema::hasTable('migrations')) {
            return;
        }

        // Only publish assets if they haven't been published yet
        $this->publishAssets();
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
            $publishedMigrations = glob($migrationsPath . '/*_create_deepseek_settings_table.php');
            if (empty($publishedMigrations)) {
                $this->publishMigrations();
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

            if (file_exists($configSource)) {
                if (!is_dir(dirname($configDest))) {
                    mkdir(dirname($configDest), 0755, true);
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
            $migrationsPath = __DIR__ . '/../../database/migrations';
            $destinationPath = database_path('migrations');

            if (is_dir($migrationsPath)) {
                $this->copyDirectory($migrationsPath, $destinationPath);
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
}
