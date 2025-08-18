<?php

namespace Filaforge\OpensourceChat\Console\Commands;

use Illuminate\Console\Command;
use Filaforge\HuggingfaceChat\Models\ModelProfile as HfModelProfile;
use Filaforge\OpensourceChat\Models\OschatModel;
use Illuminate\Support\Str;

class ImportHfModelsCommand extends Command
{
    protected $signature = 'opensource-chat:import-hf-models {--force}';
    protected $description = 'Import active HuggingFace model profiles into oschat_models';

    public function handle(): int
    {
        $this->info('Importing HuggingFace model profiles...');

        $hfRows = HfModelProfile::query()->where('provider', 'huggingface')->where('is_active', true)->get();
        $count = 0;
        foreach ($hfRows as $r) {
            $exists = OschatModel::query()->where('model_id', $r->model_id)->where('provider', 'huggingface')->exists();
            if ($exists && ! $this->option('force')) {
                $this->line('Skipping existing: '.$r->model_id);
                continue;
            }

            OschatModel::updateOrCreate(
                ['provider' => 'huggingface', 'model_id' => $r->model_id],
                [
                    'profile_id' => null,
                    'provider' => 'huggingface',
                    'name' => $r->name ?? $r->model_id,
                    'model_id' => $r->model_id,
                    'description' => $r->system_prompt ?? null,
                    'meta' => ['source' => 'hf_profile', 'hf_id' => $r->id],
                ]
            );
            $count++;
        }

        $this->info("Imported/updated {$count} models into oschat_models.");
        return 0;
    }
}
