<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		// Remove HF-specific columns from users (drop FK first if present)
		if (Schema::hasColumn('users', 'hf_last_profile_id')) {
			Schema::table('users', function (Blueprint $table) {
				try { $table->dropConstrainedForeignId('hf_last_profile_id'); }
				catch (\Throwable $e) {
					try { $table->dropForeign(['hf_last_profile_id']); } catch (\Throwable $e2) {}
					try { $table->dropColumn('hf_last_profile_id'); } catch (\Throwable $e3) {}
				}
			});
		}
		if (Schema::hasColumn('users', 'hf_api_key')) {
			Schema::table('users', function (Blueprint $table) {
				try { $table->dropColumn('hf_api_key'); } catch (\Throwable $e) {}
			});
		}

		// Drop HF tables in safe order
		$tables = [
			'hf_model_profile_usages',
			'hf_conversations',
			'hf_settings',
			'hf_model_profiles',
		];
		foreach ($tables as $t) {
			if (Schema::hasTable($t)) {
				Schema::drop($t);
			}
		}
	}

	public function down(): void
	{
		// No rollback: uninstall cleanup is irreversible
	}
};



