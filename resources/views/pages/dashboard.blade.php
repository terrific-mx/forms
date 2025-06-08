<?php

use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;
use App\Models\Form;
use Illuminate\Support\Facades\Auth;

use function Laravel\Folio\middleware;
use function Laravel\Folio\name;

name('dashboard');
middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {
    public $forms;

    public function mount()
    {
        $this->forms = Auth::user()->forms;
    }
}; ?>

<x-layouts.app :title="__('Forms')">
    @volt('pages.dashboard')
        <div class="mx-auto max-w-7xl grid gap-8">
            <div class="flex justify-between">
                <div class="grid gap-2">
                    <flux:heading level="1" size="xl">{{ __('Forms') }}</flux:heading>
                    <flux:text>{{ __('Manage your forms.') }}</flux:text>
                </div>
                <div>
                    <flux:button href="/forms/create" variant="primary" wire:navigate>{{ __('Create Form') }}</flux:button>
                </div>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Endpoint') }}</flux:table.column>
                    <flux:table.column>{{ __('Forwarding To') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($forms as $form)
                        <flux:table.row>
                            <flux:table.cell variant="strong">
                                <flux:link href="/forms/{{ $form->id }}">{{ $form->name }}</flux:link>
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:input :value="url('/f/' . $form->ulid)" variant="filled" size="sm" readonly copyable />
                            </flux:table.cell>
                            <flux:table.cell>{{ collect($form->forwardToEmails)->implode(', ') }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endvolt
</x-layouts.app>
