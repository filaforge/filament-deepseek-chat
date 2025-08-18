<?php

namespace Database\Seeders;

use Filaforge\HuggingfaceChat\Models\ModelProfile;
use Illuminate\Database\Seeder;

class ModelProfilesSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'name' => 'Llama 3 8B Instruct',
                'provider' => 'huggingface',
                'model_id' => 'meta-llama/Meta-Llama-3-8B-Instruct',
                'stream' => true,
                'timeout' => 60,
                'system_prompt' => 'You are a helpful assistant based on Llama 3 8B.',
            ],
            [
                'name' => 'Mistral 7B Instruct',
                'provider' => 'huggingface',
                'model_id' => 'mistralai/Mistral-7B-Instruct',
                'stream' => true,
                'timeout' => 60,
                'system_prompt' => 'You are a concise assistant using Mistral 7B.',
            ],
            [
                'name' => 'Phi 3 Mini 3.8B',
                'provider' => 'huggingface',
                'model_id' => 'microsoft/Phi-3-mini-4k-instruct',
                'stream' => true,
                'timeout' => 60,
                'system_prompt' => 'You are a lightweight reasoning model.',
            ],
            [
                'name' => 'Mixtral 8x7B Instruct',
                'provider' => 'huggingface',
                'model_id' => 'mistralai/Mixtral-8x7B-Instruct-v0.1',
                'stream' => true,
                'timeout' => 90,
                'system_prompt' => 'You are a powerful MoE assistant.',
            ],
        ];

        foreach ($profiles as $data) {
            ModelProfile::firstOrCreate(
                ['model_id' => $data['model_id']],
                $data
            );
        }
    }
}
