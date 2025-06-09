<x-layouts.app.header :title="$title ?? null">
    <flux:main container>
        {{ $slot }}
    </flux:main>

    @persist('toast')
        <flux:toast />
    @endpersist
</x-layouts.app.header>
