<?php

namespace Filaforge\SystemPackages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ComposerPackage extends Model
{
    protected $fillable = [
        'name',
        'version',
        'description',
        'vendor',
        'type',
        'github_url',
    ];

    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'name';

    /**
     * Get all composer packages as a collection of model instances
     */
    public static function getAllPackages(): Collection
    {
        $packages = [];

        if (class_exists(\Composer\InstalledVersions::class)) {
            try {
                $names = \Composer\InstalledVersions::getInstalledPackages();
                foreach ($names as $name) {
                    $packages[$name] = [
                        'name' => $name,
                        'version' => \Composer\InstalledVersions::getPrettyVersion($name) ?? 'N/A',
                        'description' => '',
                        'vendor' => self::extractVendor($name),
                        'type' => self::getPackageType($name),
                        'github_url' => null,
                    ];
                }
            } catch (\Throwable $e) {}
        }

        try {
            $composerLockPath = base_path('composer.lock');
            if (file_exists($composerLockPath)) {
                $composerLock = json_decode(file_get_contents($composerLockPath), true);
                foreach (['packages', 'packages-dev'] as $section) {
                    foreach (($composerLock[$section] ?? []) as $pkg) {
                        $name = (string) ($pkg['name'] ?? '');
                        if ($name === '') continue;

                        $record = $packages[$name] ?? [
                            'name' => $name,
                            'version' => $pkg['version'] ?? 'N/A',
                            'description' => '',
                            'vendor' => self::extractVendor($name),
                            'type' => self::getPackageType($name),
                            'github_url' => null,
                        ];

                        if (($record['description'] ?? '') === '' && isset($pkg['description'])) {
                            $record['description'] = (string) $pkg['description'];
                        }

                        $sourceUrl = (string) data_get($pkg, 'source.url', '');
                        $homepage = (string) ($pkg['homepage'] ?? '');
                        $githubUrl = self::resolveGithubUrl($sourceUrl ?: $homepage);
                        if ($githubUrl && empty($record['github_url'])) {
                            $record['github_url'] = $githubUrl;
                        }

                        $packages[$name] = $record;
                    }
                }
            }
        } catch (\Throwable $e) {}

        try {
            $composerJsonPath = base_path('composer.json');
            if (file_exists($composerJsonPath)) {
                $composerJson = json_decode(file_get_contents($composerJsonPath), true);
                foreach ([...array_keys($composerJson['require'] ?? []), ...array_keys($composerJson['require-dev'] ?? [])] as $name) {
                    if (! isset($packages[$name])) {
                        $packages[$name] = [
                            'name' => $name,
                            'version' => (string) (($composerJson['require'][$name] ?? $composerJson['require-dev'][$name]) ?? 'N/A'),
                            'description' => '',
                            'vendor' => self::extractVendor($name),
                            'type' => self::getPackageType($name),
                            'github_url' => null,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {}

        ksort($packages);

        return collect(array_values($packages))->map(function ($package) {
            return new static($package);
        });
    }

    protected static function extractVendor(string $packageName): string
    {
        return strtolower(strtok($packageName, '/')) ?: 'other';
    }

    protected static function getPackageType(string $name): string
    {
        if ($name === 'filament/filament') {
            return 'Core';
        }
        if (str_starts_with($name, 'filament/')) {
            return 'Extension';
        }
        if (str_starts_with($name, 'filaforge/')) {
            return 'Plugin';
        }
        if (str_contains($name, 'plugin')) {
            return 'Plugin';
        }
        return 'Unknown';
    }

    protected static function resolveGithubUrl(string $url): ?string
    {
        if ($url === '') return null;
        if (preg_match('~github.com[:/][^\s]+~i', $url, $m)) {
            $candidate = $m[0];
            $candidate = preg_replace('~^git@~', 'https://', $candidate);
            $candidate = preg_replace('~^https://github.com:~', 'https://github.com/', $candidate);
            $candidate = preg_replace('~\.git$~', '', $candidate);
            if (! str_starts_with($candidate, 'https://')) {
                $candidate = 'https://' . ltrim($candidate, '/');
            }
            return $candidate;
        }
        return null;
    }
}
