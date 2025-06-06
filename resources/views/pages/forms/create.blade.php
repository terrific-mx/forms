<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public $name = '';

    public function save()
    {
        Auth::user()->forms()->create([
            'name' => $this->name,
        ]);

        return redirect('dashboard');
    }
}; ?>

<x-layouts.app>
    @volt('pages.forms.create')
        <div>
            <form wire:submit="save">
                <flux:input wire:model="name" name="name" label="{{ __('Form Name') }}" required />
                <flux:button type="submit">{{ __('Create Form') }}</flux:button>
            </form>
        </div>
    @endvolt
</x-layouts.app>
