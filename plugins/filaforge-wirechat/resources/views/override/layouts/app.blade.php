@props(['title' => null])

<x-filament::page>
    @php($errors ??= new \Illuminate\Support\ViewErrorBag)

    <x-filament::section>
        @if($title)
            <x-slot name="heading">{{ $title }}</x-slot>
        @endif

        @wirechatStyles
        <div
            x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filaforge-wirechat', package: 'filaforge/wirechat'))]"
        >
            {{ $slot }}
        </div>
        @wirechatAssets
    </x-filament::section>
</x-filament::page>


