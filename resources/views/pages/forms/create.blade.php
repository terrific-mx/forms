<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string')]
    public $forward_to = '';

    public function save()
    {
        $this->validate();

        Auth::user()->forms()->create([
            'name' => $this->name,
            'ulid' => (string) str()->ulid(),
            'forward_to' => $this->forward_to,
        ]);

        return redirect('dashboard');
    }
}; ?>

<x-layouts.app>
    @volt('pages.forms.create')
        <div>
            <form wire:submit="save">
                <flux:input wire:model="name" name="name" label="{{ __('Form Name') }}" required />
                <flux:textarea wire:model="forward_to" name="forward_to" label="{{ __('Forward To (one email per line, optional)') }}" rows="4" />
                <flux:button type="submit">{{ __('Create Form') }}</flux:button>
            </form>
        </div>
    @endvolt
</x-layouts.app>
