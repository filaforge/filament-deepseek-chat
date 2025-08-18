
@use('Namu\WireChat\Facades\WireChat')

<header class="px-3 z-10 sticky top-0 w-full py-2 " dusk="header">


    {{-- Title/name and Icon --}}
    <section class=" justify-between flex items-center   pb-2">

        @if (isset($title))
            <div class="flex items-center gap-2 truncate  " wire:ignore>
                <h2 class=" text-2xl font-bold dark:text-white"  dusk="title">{{$title}}</h2>
            </div>
        @endif



        <div class="flex gap-x-3 items-center  ">

            @if ($showNewChatModalButton)

            <x-wirechat::actions.new-chat widget="{{$this->isWidget()}}">
                <x-filament::button id="open-new-chat-modal-button" size="sm" icon="heroicon-o-plus">
                    {{ __('wirechat::chats.labels.new_chat') ?? 'New Chat' }}
                </x-filament::button>
            </x-wirechat::actions.new-chat>
            @endif


            {{-- Only show if is not widget --}}
            @if ($showHomeRouteButton)
            <a id="redirect-button" href="{{ config('wirechat.home_route', '/') }}" class="flex items-center">
                <svg class="wc-home-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true">
                    <g fill="none" stroke="currentColor">
                        <path d="M5 12.76c0-1.358 0-2.037.274-2.634c.275-.597.79-1.038 1.821-1.922l1-.857C9.96 5.75 10.89 4.95 12 4.95s2.041.799 3.905 2.396l1 .857c1.03.884 1.546 1.325 1.82 1.922c.275.597.275 1.276.275 2.634V17c0 1.886 0 2.828-.586 3.414S16.886 21 15 21H9c-1.886 0-2.828 0-3.414-.586S5 18.886 5 17z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.5 21v-5a1 1 0 0 0-1-1h-3a1 1 0 0 0-1 1v5" />
                    </g>
                </svg>
            </a>
            @endif


        </div>



    </section>

    {{-- Search input --}}
    @if ($allowChatsSearch)
        <section class="mt-4">
            <div class="px-2 rounded-lg dark:bg-[var(--wc-dark-secondary)]  bg-[var(--wc-light-secondary)]  grid grid-cols-12 items-center">

                <label for="chats-search-field" class="col-span-1">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="wc-search-icon" aria-hidden="true">
                        <path fill="currentColor" d="m21 21-5.197-5.197A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </label>

                <input id="chats-search-field" name="chats_search" maxlength="100" type="search" wire:model.live.debounce='search'
                    placeholder="{{ __('wirechat::chats.inputs.search.placeholder')  }}" autocomplete="off"
                    class=" col-span-11 border-0  bg-inherit dark:text-white outline-hidden w-full focus:outline-hidden  focus:ring-0 hover:ring-0">

                </div>

        </section>
    @endif

</header>
