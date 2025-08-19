<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shell_terminal_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, array
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        $this->insertDefaultSettings();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shell_terminal_settings');
    }

    /**
     * Insert default settings
     */
    private function insertDefaultSettings(): void
    {
        $settings = [
            [
                'key' => 'enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable or disable the shell terminal',
            ],
            [
                'key' => 'rate_limit',
                'value' => '60',
                'type' => 'integer',
                'description' => 'Maximum commands per minute per user',
            ],
            [
                'key' => 'command_timeout',
                'value' => '300',
                'type' => 'integer',
                'description' => 'Command execution timeout in seconds',
            ],
            [
                'key' => 'max_history',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Maximum commands to keep in history',
            ],
            [
                'key' => 'log_commands',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Log all executed commands',
            ],
            [
                'key' => 'require_confirmation',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Require confirmation for dangerous commands',
            ],
            [
                'key' => 'show_welcome_message',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Show welcome message on terminal start',
            ],
            [
                'key' => 'enable_tab_completion',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable tab completion',
            ],
            [
                'key' => 'enable_command_history',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable command history navigation',
            ],
            [
                'key' => 'terminal_height',
                'value' => '60',
                'type' => 'integer',
                'description' => 'Terminal height in viewport height units',
            ],
            [
                'key' => 'dark_mode',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Use dark theme for terminal',
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('shell_terminal_settings')->insert($setting);
        }
    }
};
