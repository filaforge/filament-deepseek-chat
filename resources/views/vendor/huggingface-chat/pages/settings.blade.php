<x-filament-panels::page>
	<x-filament::section>
		<x-slot name="heading">Hugging Face API Settings</x-slot>
		<form wire:submit.prevent="save" class="space-y-4">
			<div>
				<label for="apiKey" class="block text-sm font-medium text-gray-700 dark:text-gray-300">API Token</label>
				<textarea id="apiKey" wire:model.defer="apiKey" rows="5" placeholder="Paste your Hugging Face API token here" class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 p-2 text-sm"></textarea>
			</div>
			<button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-white">Save</button>
		</form>
	</x-filament::section>
</x-filament-panels::page>



