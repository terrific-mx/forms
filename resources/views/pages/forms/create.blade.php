<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    #[Validate('required|string|max:255')]
    public $name = '';

    #[Validate('nullable|string')]
    public $forward_to = '';
    public array $forward_to_emails = [];

    public function save()
    {
        $this->forward_to_emails = explode(PHP_EOL, $this->forward_to);

        $this->validate([
            'name' => 'required|string|max:255',
            'forward_to' => 'nullable|string',
            'forward_to_emails.*' => 'email',
        ]);

        Auth::user()->forms()->create([
            'name' => $this->name,
            'ulid' => (string) str()->ulid(),
            'forward_to' => implode("\n", $this->forward_to_emails),
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
