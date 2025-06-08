<x-layouts.guest>
    <div class="mx-auto min-h-screen flex flex-col justify-center">
        <flux:container>
            <flux:card class="max-w-md flex flex-col items-center mx-auto gap-8">
                <flux:heading size="lg" class="text-center">{{ __('Thanks for your submission!') }}</flux:heading>
                <flux:brand href="/" :name="config('app.name')" class="max-lg:hidden" wire:navigate>
                    <svg class="w-4 text-zinc-950 dark:text-white/84" width="34" height="48" viewBox="0 0 34 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.45659 17.2143L24.3027 8.64286L15.395 3.5L0.549161 12.0713L0.548828 29.2139L9.45652 34.3568L9.45659 17.2143Z" fill="currentColor"/>
                        <path d="M33.4453 17.7854L33.4453 34.9283L18.5991 43.4994L9.69141 38.3565L24.5376 29.7851L24.5376 12.6426L33.4453 17.7854Z" fill="currentColor"/>
                    </svg>
                </flux:brand>
            </flux:card>
        </flux:container>
    </div>
</x-layouts.guest>
