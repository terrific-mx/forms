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
            $this->sortDirection = 'asc';
        }
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
        <div class="mx-auto max-w-7xl grid gap-8">
            <div class="grid gap-2">
                <flux:heading level="1" size="xl">{{ $form->name }}</flux:heading>
                <flux:text>{{ __('Form submissions') }}</flux:text>
            </div>
            <flux:table :paginate="$this->submissions">
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Data Excerpt') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')" align="end">{{ __('Date') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->submissions as $submission)
                        <flux:table.row :key="$submission->id">
                            <flux:table.cell>
                                <div class="flex items-center gap-2 sm:gap-4">
                                    <flux:avatar circle size="lg" class="max-sm:size-8" :src="$submission->avatar_url" />
                                    <div class="flex flex-col">
                                        <flux:heading>
                                            @if ($submission->name)
                                                {{ $submission->name }}
                                            @else
                                                {{ __('Anonymous') }}
                                            @endif
                                        </flux:heading>
                                        @if ($submission->email)
                                            <flux:text class="max-sm:hidden">{{ $submission->email }}</flux:text>
                                        @endif
                                    </div>
                                </div>
                            </flux:table.cell>
                            <flux:table.cell>
                                <div class="line-clamp-1">
                                    {{ $submission->excerpt }}
                                </div>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">
                                <div class="flex justify-end items-center gap-2 sm:gap-4">
                                    {{ $submission->formatted_created_at }}
                                    <flux:tooltip toggleable>
                                        <flux:button icon="information-circle" size="sm" variant="ghost" />
                                        <flux:tooltip.content class="max-w-[20rem] space-y-2">
                                            <p>IP Address: {{ $submission->ip_address }}</p>
                                            <p>User Agent: {{ $submission->user_agent ?? 'N/A' }}</p>
                                            @if ($submission->referrer)
                                                <p>Referrer: <a href="{{ $submission->referrer }}" target="_blank" rel="noopener noreferrer" class="underline">{{ $submission->referrer }}</a></p>
                                            @endif
                                        </flux:tooltip.content>
                                    </flux:tooltip>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endvolt
</x-layouts.app>
