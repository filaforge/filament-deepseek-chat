@php($errors ??= new \Illuminate\Support\ViewErrorBag)
<x-filament::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}

        <x-filament::actions
            :actions="[
                \Filament\Actions\Action::make('save')
                    ->label('Save Settings')
                    ->submit('save')
                    ->color('primary'),
            ]"
        />
    </form>
</x-filament::page>
