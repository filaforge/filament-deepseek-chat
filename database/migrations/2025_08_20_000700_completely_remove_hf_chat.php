<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		// Remove HF-specific columns from users table
		Schema::table('users', function (Blueprint $table) {
			// Drop foreign key first if it exists
			if (Schema::hasColumn('users', 'hf_last_profile_id')) {
				try {
					$table->dropConstrainedForeignId('hf_last_profile_id');
				} catch (\Throwable $e) {
					try {
						$table->dropForeign(['hf_last_profile_id']);
					} catch (\Throwable $e2) {}
					try {
						$table->dropColumn('hf_last_profile_id');
					} catch (\Throwable $e3) {}
				}
			}

			// Drop API key column
			if (Schema::hasColumn('users', 'hf_api_key')) {
				$table->dropColumn('hf_api_key');
			}
		});

		// Drop all HF tables in safe order (respecting foreign keys)
		$tables = [
			'hf_model_profile_usages',    // Child table first
			'hf_conversations',           // Child table
			'hf_settings',                // Child table
			'hf_model_profiles',          // Parent table last
		];

		foreach ($tables as $tableName) {
			if (Schema::hasTable($tableName)) {
				Schema::dropIfExists($tableName);
			}
		}
	}

	public function down(): void
	{
		// No rollback - complete removal is irreversible
	}
};

