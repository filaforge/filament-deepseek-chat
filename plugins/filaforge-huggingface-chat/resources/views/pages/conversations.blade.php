<x-filament-panels::page>
	<x-filament::section>
		<x-slot name="heading">HF Conversations</x-slot>
		<div>
			{{ $this->table ?? '' }}
		</div>
	</x-filament::section>
</x-filament-panels::page>


