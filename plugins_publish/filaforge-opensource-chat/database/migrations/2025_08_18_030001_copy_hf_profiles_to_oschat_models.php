<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Copy rows from hf_model_profiles into oschat_models when model_id/provider not already present
        $rows = DB::table('hf_model_profiles')->get();
        foreach ($rows as $r) {
            $exists = DB::table('oschat_models')
                ->where('provider', $r->provider)
                ->where('model_id', $r->model_id)
                ->exists();
            if ($exists) {
                continue;
            }

            DB::table('oschat_models')->insert([
                'profile_id' => null,
                'provider' => $r->provider,
                'name' => $r->name,
                'model_id' => $r->model_id,
                'base_url' => $r->base_url ?? null,
                'api_key' => $r->api_key ?? null,
                'stream' => isset($r->stream) ? (bool) $r->stream : true,
                'timeout' => $r->timeout ?? 60,
                'system_prompt' => $r->system_prompt ?? null,
                'extra' => isset($r->extra) ? $r->extra : null,
                'per_minute_limit' => $r->per_minute_limit ?? null,
                'per_day_limit' => $r->per_day_limit ?? null,
                'is_active' => isset($r->is_active) ? (bool) $r->is_active : true,
                'description' => null,
                'meta' => json_encode(['imported_from' => 'hf_model_profiles', 'hf_id' => $r->id]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        // no-op: keep imported rows
    }
};
