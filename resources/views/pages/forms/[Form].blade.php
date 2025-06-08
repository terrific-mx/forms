<?php

use App\Models\Form;
use Illuminate\Database\Eloquent\Collection;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {
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

    #[\Livewire\Attributes\Computed]
    public function submissions()
    {
        return $this->form->submissions()
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }
}; ?>

<x-layouts.app>
    @volt('pages.forms.show')
        <div class="mx-auto max-w-5xl grid gap-8">
            <div class="grid gap-2">
                <flux:heading level="1" size="xl">{{ $form->name }}</flux:heading>
                <flux:text>{{ __('Form submissions') }}</flux:text>
            </div>
            <flux:table :paginate="$this->submissions">
                <flux:table.columns>
                    <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection" wire:click="sort('created_at')">{{ __('Date') }}</flux:table.column>
                    <flux:table.column sortable :sorted="$sortBy === 'ip_address'" :direction="$sortDirection" wire:click="sort('ip_address')">{{ __('IP Address') }}</flux:table.column>
                    <flux:table.column>{{ __('Referrer') }}</flux:table.column>
                    <flux:table.column>{{ __('Data') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($this->submissions as $submission)
                        <flux:table.row :key="$submission->id">
                            <flux:table.cell class="whitespace-nowrap">{{ $submission->formatted_created_at }}</flux:table.cell>
                            <flux:table.cell>{{ $submission->ip_address }}</flux:table.cell>
                            <flux:table.cell>{{ $submission->referrer }}</flux:table.cell>
                            <flux:table.cell>
                                @foreach ($submission->data as $key => $value)
                                    <div class="line-clamp-1"><span>{{ $key }}:</span> {{ is_array($value) ? json_encode($value) : $value }}</div>
                                @endforeach
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endvolt
</x-layouts.app>
