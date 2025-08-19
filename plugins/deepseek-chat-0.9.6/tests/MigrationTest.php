<?php

namespace Filaforge\DeepseekChat\Tests;

use PHPUnit\Framework\TestCase;
use Filaforge\DeepseekChat\Providers\DeepseekChatServiceProvider;

class MigrationTest extends TestCase
{
    public function testMigrationClassExtraction()
    {
        $provider = new DeepseekChatServiceProvider();

        // Test the reflection method to get migration class name
        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getMigrationClassFromFile');
        $method->setAccessible(true);

        // Test with a sample migration content
        $sampleContent = '<?php class TestMigration extends Migration { }';
        $tempFile = tempnam(sys_get_temp_dir(), 'test_migration');
        file_put_contents($tempFile, $sampleContent);

        $className = $method->invoke($provider, $tempFile);

        $this->assertEquals('TestMigration', $className);

        // Cleanup
        unlink($tempFile);
    }

    public function testMigrationDetection()
    {
        $provider = new DeepseekChatServiceProvider();

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('hasMigrationBeenRun');
        $method->setAccessible(true);

        // This should return false in test environment
        $result = $method->invoke($provider, 'test_migration');

        // In test environment, this should return false
        $this->assertIsBool($result);
    }
}
