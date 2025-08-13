<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FilaforgePluginsSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Use the existing sqlite database file for faster setup in CI.
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', database_path('database.sqlite'));

        // Authenticate to satisfy Filament auth expectations during component mount.
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /** @test */
    public function api_explorer_page_mounts(): void
    {
        Livewire::test(\Filaforge\ApiExplorer\Pages\ApiExplorerPage::class)
            ->assertOk();
    }

    /** @test */
    public function database_query_page_mounts(): void
    {
        Livewire::test(\Filaforge\DatabaseQuery\Pages\DatabaseQuery::class)
            ->assertOk();
    }

    /** @test */
    public function database_viewer_page_mounts(): void
    {
        Livewire::test(\Filaforge\DatabaseViewer\Pages\DatabaseViewer::class)
            ->assertOk();
    }

    /** @test */
    public function terminal_console_page_mounts(): void
    {
        Livewire::test(\Filaforge\TerminalConsole\Pages\TerminalPage::class)
            ->assertOk();
    }

    /** @test */
    public function system_packages_page_mounts(): void
    {
        Livewire::test(\Filaforge\SystemPackages\Pages\SystemPackagesPage::class)
            ->assertOk();
    }

    /** @test */
    public function system_tools_page_mounts(): void
    {
        Livewire::test(\Filaforge\SystemTools\Pages\SystemTools::class)
            ->assertOk();
    }

    /** @test */
    public function deepseek_chat_page_mounts(): void
    {
        Livewire::test(\Filaforge\DeepseekChat\Pages\DeepseekChatPage::class)
            ->assertOk();
    }

    /** @test */
    public function hello_widget_component_mounts(): void
    {
        Livewire::test(\Filaforge\HelloWidget\Filament\Widgets\HelloWidget::class)
            ->assertOk();
    }

    /** @test */
    public function system_monitor_widgets_mount(): void
    {
        Livewire::test(\Filaforge\SystemMonitor\Widgets\SystemMonitorWidget::class)->assertOk();
        Livewire::test(\Filaforge\SystemMonitor\Widgets\SystemInfoWidget::class)->assertOk();
    }

    /** @test */
    public function system_widget_component_mounts(): void
    {
        Livewire::test(\Filaforge\SystemWidget\Filament\Widgets\SystemMonitorWidget::class)
            ->assertOk();
    }
}
