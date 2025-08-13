<?php

namespace Filaforge\DeepseekChat\Pages\Actions;

use Filament\Actions\Action;
use Filament\Forms; // For form components

class SetApiKey
{
    public static function make(): Action
    {
        return Action::make('setApiKey')
            ->label('Set API Key')
            ->extraAttributes(['class' => 'ml-auto'])
            ->form([
                Forms\Components\Textarea::make('deepseek_api_key')
                    ->label('DeepSeek API Key')
                    ->rows(4)
                    ->required(),
            ])
            ->action(function (array $data): void {
                $user = auth()->user();
                if ($user) {
                    $user->forceFill(['deepseek_api_key' => $data['deepseek_api_key']])->save();
                }
            });
    }
}
