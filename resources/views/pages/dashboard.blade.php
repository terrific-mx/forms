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
        <div>
            <flux:button href="/forms/create">{{ __('Create Form') }}</flux:button>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach ($forms as $form)
                        <flux:table.row>
                            <flux:table.cell>{{ $form->name }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    @endvolt
</x-layouts.app>
