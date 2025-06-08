<?php

use App\Models\FormSubmission;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {
    public FormSubmission $submission;

    public function mount(FormSubmission $formSubmission)
    {
        $this->submission = $formSubmission;
    }
}; ?>

<x-layouts.app>
    @volt('pages.submissions.show')
        <div class="mx-auto max-w-4xl">
            <!-- Header -->
            <div class="max-lg:hidden">
                <flux:text>
                    <flux:link
                        variant="subtle"
                        wire:navigate
                        href="/forms/{{ $submission->form->id }}"
                        class="inline-flex items-center gap-2"
                    >
                        <flux:icon name="arrow-left" variant="micro" />
                        {{ $submission->form->name }}
                    </flux:link>
                </flux:text>
            </div>

            <!-- Submission Header -->
            <div class="mt-4 lg:mt-8">
                <div class="flex items-center gap-4">
                    <flux:avatar :src="$submission->avatar_url" circle />
                    <div>
                        <div class="flex items-center gap-4">
                            <flux:heading level="1">Submission #{{ $submission->id }}</flux:heading>
                            <flux:badge color="green" size="sm" inset="top bottom">{{ __('New') }}</flux:badge>
                        </div>
                        <flux:text>
                            {{ __('From') }} {{ $submission->form->name }}
                        </flux:text>
                    </div>
                </div>

                <div class="isolate mt-6 flex flex-wrap justify-between gap-x-6 gap-y-4">
                    <div class="flex flex-wrap gap-x-8 gap-y-4">
                        <flux:text class="flex items-center gap-2">
                            <flux:icon name="user" variant="micro" />
                            {{ $submission->name ?: __('Anonymous') }}
                        </flux:text>

                        @if ($submission->email)
                            <flux:text class="flex items-center gap-2">
                                <flux:icon name="envelope" variant="micro" />
                                <flux:link href="mailto:{{ $submission->email }}" variant="subtle">{{ $submission->email }}</flux:link>
                            </flux:text>
                        @endif

                        <flux:text class="flex items-center gap-2">
                            <flux:icon name="calendar" variant="micro" />
                            {{ $submission->formatted_created_at }}
                            @include('partials.submission-info-tooltip', [
                                'submission' => $submission,
                                'size' => 'xs',
                                'class' => ''
                            ])
                        </flux:text>
                    </div>

                    <div class="flex gap-3">
                        <flux:button size="sm" variant="ghost" icon="exclamation-triangle">
                            {{ __('Mark as Spam') }}
                        </flux:button>
                        <flux:button variant="primary" size="sm" icon="arrow-uturn-right">
                            {{ __('Reply') }}
                        </flux:button>
                    </div>
                </div>
            </div>

            <!-- Form Data Section -->
            @if (!empty($submission->data))
                <div class="mt-12">
                    <flux:heading level="2">{{ __('Form Data') }}</flux:heading>
                    <flux:separator class="mt-4" />

                    <x-description-list>
                        @foreach ($submission->data as $key => $value)
                            <x-description-list.term>
                                <flux:text>{{ str_replace('_', ' ', Str::title($key)) }}</flux:text>
                            </x-description-list.term>
                            <x-description-list.details>
                                @include('partials.submission-value', ['value' => $value])
                            </x-description-list.details>
                        @endforeach
                    </x-description-list>
                </div>
            @else
                <div class="mt-12">
                    <flux:heading level="2">{{ __('Form Data') }}</flux:heading>
                    <flux:separator class="mt-4" />
                    <flux:text class="text-zinc-500 dark:text-zinc-400 italic">
                        {{ __('No form data was submitted.') }}
                    </flux:text>
                </div>
            @endif

            <!-- Technical Details Section -->
            <div class="mt-12">
                <flux:heading level="2">{{ __('Technical Details') }}</flux:heading>
                <flux:separator class="mt-4" />

                <x-description-list>
                    <x-description-list.term>
                        <flux:text>{{ __('Submission ID') }}</flux:text>
                    </x-description-list.term>
                    <x-description-list.details>
                        <flux:text variant="strong" class="font-mono">{{ $submission->id }}</flux:text>
                    </x-description-list.details>

                    @if ($submission->ip_address)
                        <x-description-list.term>
                            <flux:text>{{ __('IP Address') }}</flux:text>
                        </x-description-list.term>
                        <x-description-list.details>
                            <flux:text variant="strong">{{ $submission->ip_address }}</flux:text>
                        </x-description-list.details>
                    @endif

                    @if ($submission->user_agent)
                        <x-description-list.term>
                            <flux:text>{{ __('User Agent') }}</flux:text>
                        </x-description-list.term>
                        <x-description-list.details>
                            <flux:text variant="strong" class="break-all">{{ $submission->user_agent }}</flux:text>
                        </x-description-list.details>
                    @endif

                    @if ($submission->referrer)
                        <x-description-list.term>
                            <flux:text>{{ __('Referrer') }}</flux:text>
                        </x-description-list.term>
                        <x-description-list.details>
                            <flux:text>
                                <flux:link href="{{ $submission->referrer }}" target="_blank" rel="noopener noreferrer" variant="strong" class="break-all">
                                    {{ $submission->referrer }}
                                </flux:link>
                            </flux:text>
                        </x-description-list.details>
                    @endif
                </x-description-list>
            </div>
        </div>
    @endvolt
</x-layouts.app>
