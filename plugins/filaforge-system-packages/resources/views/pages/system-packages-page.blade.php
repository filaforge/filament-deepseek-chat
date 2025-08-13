<x-filament::page>
	<div class="space-y-6">
		{{ $this->table }}

		<x-filament::section icon="heroicon-o-document-text" heading="Composer Files">
			<div class="flex items-center gap-3">
				<x-filament::link href="/composer.json" icon="heroicon-m-document-text" target="_blank">composer.json</x-filament::link>
				<x-filament::link href="/composer.lock" icon="heroicon-m-document-text" target="_blank">composer.lock</x-filament::link>
			</div>
		</x-filament::section>
	</div>
</x-filament::page>
