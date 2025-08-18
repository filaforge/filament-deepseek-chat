@props([
    'widget' => false
])

<button type="button" class="fi-btn fi-color-primary fi-btn-size-sm" @click="$wire.dispatch('open-modal', { id: 'wirechat-new-chat' })">
    {{ $slot }}
</button>
