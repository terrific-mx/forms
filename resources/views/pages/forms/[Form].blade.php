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
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8">
                <div>
                    <flux:heading level="1" size="xl">{{ $form->name }}</flux:heading>
                    <flux:text>
                        {{ $this->submissions->total() }} {{ Str::plural('submission', $this->submissions->total()) }}
                    </flux:text>
                </div>

                <div class="flex gap-3">
                    {{-- <flux:button variant="ghost" size="sm" icon="cog-6-tooth">
                        {{ __('Settings') }}
                    </flux:button> --}}
                </div>
            </div>

            <!-- Submissions Table -->
            <flux:table :paginate="$this->submissions">
                <flux:table.columns>
                    <flux:table.column>{{ __('Submitter') }}</flux:table.column>
                    <flux:table.column>{{ __('Data Excerpt') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')" align="end">{{ __('Submitted') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->submissions as $submission)
                        <flux:table.row :key="$submission->id" wire:navigate href="/submissions/{{ $submission->id }}" class="cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-900/50">
                            <flux:table.cell>
                                <div class="flex items-center gap-3">
                                    <flux:avatar
                                        circle
                                        size="sm"
                                        :src="$submission->avatar_url"
                                    />
                                    <div class="min-w-0 flex-1">
                                        <flux:heading size="sm" class="truncate">
                                            {{ $submission->name ?: __('Anonymous') }}
                                        </flux:heading>
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
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3" class="text-center py-12">
                                <div class="flex flex-col items-center gap-3">
                                    <flux:icon name="inbox" class="size-12 text-zinc-400" />
                                    <div>
                                        <flux:heading size="sm">{{ __('No submissions yet') }}</flux:heading>
                                        <flux:text size="sm">
                                            {{ __('Submissions will appear here once your form receives responses.') }}
                                        </flux:text>
                                    </div>
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    @endvolt
</x-layouts.app>
