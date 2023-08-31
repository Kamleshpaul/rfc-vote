@php
    /**
     * @var Illuminate\View\ComponentSlot $slot
     * @var array{id: int, name: string, url: string, contributions: string[]}[] $contributors
     */
@endphp

<x-about.section heading="Our contributors">
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach($contributors as $c)
            <a href="{{ $c->url }}" target="_blank" @class([
                'flex gap-3 items-center transition-all duration-300 bg-background w-full',
                'rounded-[50px_13px_13px_50px] pr-2 hover:bg-admin-navbar-background',
            ])>
                <img
                    src="{{ "https://avatars.githubusercontent.com/u/{$c->id}" }}"
                    alt="{{ $c->name }}"
                    width="460"
                    height="460"
                    class="w-12 h-12 md:w-16 md:h-16 shadow-md rounded-full"
                />

                <div class="flex justify-center">
                    <div class="flex-col justify-center text-[.9rem] leading-5 flex my-0.5">
                        <b>{{ $c->name }}</b>
                        <span class="opacity-60 text-xs md:text-md">
                            {{ implode(', ', $c->contributions) }}
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</x-about.section>
