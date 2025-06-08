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
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center gap-4">
                    <flux:button
                        icon="arrow-left"
                        variant="ghost"
                        wire:navigate
                        href="/forms/{{ $submission->form->id }}"
                    >
                        {{ $submission->form->name }}
                    </flux:button>
                </div>
            </div>

            <!-- Submission Header -->
            <div class="flex items-center justify-between mb-2.5">
                <div class="flex items-center gap-4">
                    <flux:heading level="1" size="lg">Submission #{{ $submission->id }}</flux:heading>
                    <flux:badge color="green" size="sm">{{ __('Received') }}</flux:badge>
                </div>
                <div class="flex items-center gap-2">
                    <flux:button variant="outline" size="sm">
                        Export
                    </flux:button>
                    <flux:button variant="filled" size="sm">
                        Forward
                    </flux:button>
                </div>
            </div>

            <!-- Submission Details -->
            <div class="flex items-center gap-6 text-sm text-gray-600">
                <div class="flex items-center gap-2">
                    <flux:icon name="user" variant="micro" />
                    @if ($submission->name)
                        {{ $submission->name }}
                    @else
                        Anonymous
                    @endif
                </div>
                @if ($submission->email)
                    <div class="flex items-center gap-2">
                        <flux:icon name="envelope" variant="micro" />
                        {{ $submission->email }}
                    </div>
                @endif
                <div class="flex items-center gap-2">
                    <flux:icon name="calendar" variant="micro" />
                    {{ $submission->formatted_created_at }}
                    <flux:tooltip toggleable>
                        <flux:button icon="information-circle" size="xs" variant="ghost" inset="left" />
                        <flux:tooltip.content class="max-w-[20rem] space-y-2">
                            <p>{{ __('IP Address: ') }}{{ $submission->ip_address }}</p>
                            <p>{{ __('User Agent: ') }}{{ $submission->user_agent ?? 'N/A' }}</p>
                            @if ($submission->referrer)
                                <p>{{ __('Referrer: ') }}<a href="{{ $submission->referrer }}" target="_blank" rel="noopener noreferrer" class="underline">{{ $submission->referrer }}</a></p>
                            @endif
                        </flux:tooltip.content>
                    </flux:tooltip>
                </div>
            </div>

            <!-- Summary Section -->
            <div class="mt-12">
                <flux:heading level="2">{{ __('Summary') }}</flux:heading>
                <flux:separator class="mt-4" />

                <x-description-list>
                    <x-description-list.term>
                        <flux:text>{{ __('Submitter') }}</flux:text>
                    </x-description-list.term>
                    <x-description-list.details>
                        <div class="flex items-center gap-3">
                            <flux:avatar
                                circle
                                size="sm"
                                :src="$submission->avatar_url"
                            />
                            <div>
                                <flux:text variant="strong">
                                    @if ($submission->name)
                                        {{ $submission->name }}
                                    @else
                                        {{ __('Anonymous') }}
                                    @endif
                                </flux:text>
                                @if ($submission->email)
                                    <flux:text>{{ $submission->email }}</flux:text>
                                @endif
                            </div>
                        </div>
                    </x-description-list.details>

                    <x-description-list.term>
                        <flux:text>{{ __('Form') }}</flux:text>
                    </x-description-list.term>
                    <x-description-list.details>
                        <flux:text><flux:link href="/forms/{{ $submission->form->id }}">{{ $submission->form->name }}</flux:link></flux:text>
                    </x-description-list.details>

                    <x-description-list.term>
                        <flux:text>{{ __('Submitted') }}</flux:text>
                    </x-description-list.term>
                    <x-description-list.details>
                        <flux:text variant="strong">{{ $submission->formatted_created_at }}</flux:text>
                    </x-description-list.details>

                    @if ($submission->ip_address)
                        <x-description-list.term>
                            <flux:text>{{ __('IP Address') }}</flux:text>
                        </x-description-list.term>
                        <x-description-list.details>
                            <flux:text variant="strong">{{ $submission->ip_address }}</flux:text>
                        </x-description-list.details>
                    @endif

                    @if (count($submission->data) > 0)
                        <x-description-list.term>
                            <flux:text>{{ __('Fields Count') }}</flux:text>
                        </x-description-list.term>
                        <x-description-list.details>
                            <flux:text variant="strong">{{ count($submission->data) }} {{ Str::plural('field', count($submission->data)) }}</flux:text>
                        </x-description-list.details>
                    @endif
                </x-description-list>
            </div>

            <!-- Form Data Section -->
            @if (count($submission->data) > 0)
                <div class="mt-12">
                    <flux:heading level="2">{{ __('Form Data') }}</flux:heading>
                    <flux:separator class="mt-4" />

                    <x-description-list>
                        @foreach ($submission->data as $key => $value)
                            <x-description-list.term>
                                <flux:text>{{ str_replace('_', ' ', Str::title($key)) }}</flux:text>
                            </x-description-list.term>
                            <x-description-list.details>
                                @if (is_array($value))
                                    <div class="space-y-1">
                                        @foreach ($value as $item)
                                            <flux:text variant="strong" class="block">{{ $item }}</flux:text>
                                        @endforeach
                                    </div>
                                @elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL))
                                    <flux:text>
                                        <flux:link href="mailto:{{ $value }}" variant="strong">{{ $value }}</flux:link>
                                    </flux:text>
                                @elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_URL))
                                    <flux:text>
                                        <flux:link href="{{ $value }}" target="_blank" rel="noopener noreferrer" variant="strong">{{ $value }}</flux:link>
                                    </flux:text>
                                @elseif (strlen($value) > 100)
                                    <flux:text variant="strong" class="whitespace-pre-wrap">{{ $value }}</flux:text>
                                @else
                                    <flux:text variant="strong">{{ $value }}</flux:text>
                                @endif
                            </x-description-list.details>
                        @endforeach
                    </x-description-list>
                </div>
            @endif

            <!-- Technical Details Section -->
            <div class="mt-12">
                <flux:heading level="2">{{ __('Technical Details') }}</flux:heading>
                <flux:separator class="mt-4" />

                <x-description-list>
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

                    <x-description-list.term>
                        <flux:text>{{ __('Submission ID') }}</flux:text>
                    </x-description-list.term>
                    <x-description-list.details>
                        <flux:text variant="strong" class="font-mono">{{ $submission->id }}</flux:text>
                    </x-description-list.details>

                    <x-description-list.term>
                        <flux:text>{{ __('Created At') }}</flux:text>
                    </x-description-list.term>
                    <x-description-list.details>
                        <flux:text variant="strong">{{ $submission->created_at->format('M j, Y \a\t g:i:s A') }}</flux:text>
                    </x-description-list.details>
                </x-description-list>
            </div>
        </div>
    @endvolt
</x-layouts.app>
