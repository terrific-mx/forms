<?php

use App\Models\Form;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Livewire\WithPagination;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {
    use WithPagination;

    public Form $form;
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    public function sort($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'desc'; // Default to desc for better UX
        }

        $this->resetPage(); // Reset pagination when sorting
    }

    #[Computed]
    public function newSubmissions()
    {
        return $this->form->submissions()
            ->new()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    #[Computed]
    public function seenSubmissions()
    {
        return $this->form->submissions()
            ->seen()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    #[Computed]
    public function submissions()
    {
        return $this->form->submissions()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }
}; ?>

<x-layouts.app>
    @volt('pages.forms.show')
        <div class="mx-auto max-w-7xl">
            <!-- Header -->
            <div class="max-lg:hidden">
                <flux:text>
                    <flux:link
                        variant="subtle"
                        wire:navigate
                        href="/dashboard"
                        class="inline-flex items-center gap-2"
                    >
                        <flux:icon name="arrow-left" variant="micro" />
                        {{ __('Dashboard') }}
                    </flux:link>
                </flux:text>
            </div>

            <!-- Form Header -->
            <div class="mt-4 lg:mt-8">
                <div class="flex items-center gap-4">
                    @if($form->logo_url)
                        <flux:avatar :src="$form->logo_url" size="lg" />
                    @endif
                    <div>
                        <div class="flex items-center gap-4">
                            <flux:heading level="1" size="xl">{{ $form->name }}</flux:heading>
                        </div>
                        <flux:text size="sm">
                            {{ $this->submissions->total() }} {{ Str::plural('submission', $this->submissions->total()) }}
                            ({{ $this->newSubmissions->total() }} new, {{ $this->seenSubmissions->total() }} seen)
                        </flux:text>
                    </div>
                </div>

                <div class="isolate mt-6 flex flex-wrap justify-between gap-x-6 gap-y-4">
                    <div class="flex flex-wrap gap-x-8 gap-y-4">
                        <flux:text class="flex items-center gap-2">
                            <flux:icon name="document-text" variant="micro" />
                            {{ __('Form ID') }}: {{ $form->id }}
                        </flux:text>

                        <flux:text class="flex items-center gap-2">
                            <flux:icon name="calendar" variant="micro" />
                            {{ __('Created') }} {{ $form->created_at->format('M j, Y') }}
                        </flux:text>

                        @if($form->forward_to)
                            <flux:text class="flex items-center gap-2">
                                <flux:icon name="envelope" variant="micro" />
                                {{ __('Forwarding enabled') }}
                            </flux:text>
                        @endif
                    </div>

                    <div class="flex gap-3">
                        <flux:button href="/forms/{{ $form->id }}/settings" variant="ghost" size="sm" icon="cog-6-tooth" wire:navigate>
                            {{ __('Settings') }}
                        </flux:button>
                    </div>
                </div>
            </div>

            @if($this->newSubmissions->count() > 0)
                <!-- New Submissions Table -->
                <div class="mt-14">
                    <div class="flex items-center gap-2">
                        <flux:heading level="2">{{ __('New Submissions') }}</flux:heading>
                        <flux:badge color="green" size="sm" inset="top bottom">{{ $this->newSubmissions->total() }}</flux:badge>
                    </div>

                    <flux:table :paginate="$this->newSubmissions" class="mt-4">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Submitter') }}</flux:table.column>
                            <flux:table.column>{{ __('Data Excerpt') }}</flux:table.column>
                            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')" align="end">{{ __('Submitted') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->newSubmissions as $submission)
                                <flux:table.row :key="$submission->id" wire:navigate href="/submissions/{{ $submission->id }}" class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                    <flux:table.cell>
                                        <div class="flex items-center gap-3">
                                            <flux:avatar
                                                circle
                                                size="sm"
                                                :src="$submission->avatar_url"
                                            />
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <flux:heading size="sm" class="truncate">
                                                        {{ $submission->name ?: __('Anonymous') }}
                                                    </flux:heading>
                                                    <flux:badge color="green" size="sm" inset="top bottom">{{ __('New') }}</flux:badge>
                                                </div>
                                                @if ($submission->email)
                                                    <flux:text size="sm" class="truncate">
                                                        {{ $submission->email }}
                                                    </flux:text>
                                                @endif
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="line-clamp-2 text-sm">
                                            {{ $submission->excerpt ?: __('No data submitted') }}
                                        </flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        <div class="flex justify-end items-center gap-3">
                                            <div class="text-right">
                                                <flux:text size="sm" class="block">
                                                    {{ $submission->formatted_created_at }}
                                                </flux:text>
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif

            @if($this->seenSubmissions->count() > 0)
                <!-- Seen Submissions Table -->
                <div class="mt-14">
                    <div class="flex items-center gap-2">
                        <flux:heading level="2">{{ __('Previous Submissions') }}</flux:heading>
                        <flux:badge color="zinc" size="sm" inset="top bottom">{{ $this->seenSubmissions->total() }}</flux:badge>
                    </div>

                    <flux:table :paginate="$this->seenSubmissions" class="mt-4">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Submitter') }}</flux:table.column>
                            <flux:table.column>{{ __('Data Excerpt') }}</flux:table.column>
                            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')" align="end">{{ __('Submitted') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->seenSubmissions as $submission)
                                <flux:table.row :key="$submission->id" wire:navigate href="/submissions/{{ $submission->id }}" class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                                    <flux:table.cell>
                                        <div class="flex items-center gap-3">
                                            <flux:avatar
                                                circle
                                                size="sm"
                                                :src="$submission->avatar_url"
                                            />
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <flux:heading size="sm" class="truncate">
                                                        {{ $submission->name ?: __('Anonymous') }}
                                                    </flux:heading>
                                                </div>
                                                @if ($submission->email)
                                                    <flux:text size="sm" class="truncate">
                                                        {{ $submission->email }}
                                                    </flux:text>
                                                @endif
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        <flux:text class="line-clamp-2 text-sm">
                                            {{ $submission->excerpt ?: __('No data submitted') }}
                                        </flux:text>
                                    </flux:table.cell>
                                    <flux:table.cell class="whitespace-nowrap">
                                        <div class="flex justify-end items-center gap-3">
                                            <div class="text-right">
                                                <flux:text size="sm" class="block">
                                                    {{ $submission->formatted_created_at }}
                                                </flux:text>
                                            </div>
                                        </div>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endif

            @if($this->newSubmissions->count() == 0 && $this->seenSubmissions->count() == 0)
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="flex flex-col items-center gap-3">
                        <flux:icon name="inbox" class="size-12 text-zinc-400" />
                        <div>
                            <flux:heading size="sm">{{ __('No submissions yet') }}</flux:heading>
                            <flux:text size="sm">
                                {{ __('Submissions will appear here once your form receives responses.') }}
                            </flux:text>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endvolt
</x-layouts.app>
