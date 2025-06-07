<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark antialiased">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:brand href="/" :name="config('app.name')" class="max-lg:hidden" wire:navigate>
                <svg class="w-4 text-zinc-950 dark:text-white/84" width="34" height="48" viewBox="0 0 34 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9.45659 17.2143L24.3027 8.64286L15.395 3.5L0.549161 12.0713L0.548828 29.2139L9.45652 34.3568L9.45659 17.2143Z" fill="currentColor"/>
                    <path d="M33.4453 17.7854L33.4453 34.9283L18.5991 43.4994L9.69141 38.3565L24.5376 29.7851L24.5376 12.6426L33.4453 17.7854Z" fill="currentColor"/>
                </svg>
            </flux:brand>

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
            </flux:navbar>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="top" align="end">
                <flux:profile
                    :avatar="auth()->user()->avatar ?: null"
                    :initials="auth()->user()->initials()"
                    class="cursor-pointer"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-sm">
                                    @if (auth()->user()->avatar)
                                        <img src="{{ auth()->user()->avatar }}" />
                                    @else
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-sm bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    @endif
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar stashable sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="ms-1 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')">
                    <flux:navlist.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                    {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist>
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
