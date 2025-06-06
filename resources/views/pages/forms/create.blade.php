<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public $name = '';
    public $forward_to = '';
    public array $forward_to_emails = [];

    public function save()
    {
        $this->forward_to_emails = array_filter(array_map('trim', preg_split('/\r?\n/', $this->forward_to)));

        $this->validate([
            'name' => 'required|string|max:255',
            'forward_to' => 'nullable|string',
            'forward_to_emails.*' => empty($this->forward_to) ? '' : 'email',
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
        <div class="w-full max-w-md mx-auto">
            <form wire:submit="save" class="space-y-5">
                <flux:input wire:model="name" name="name" label="{{ __('Form Name') }}" required />
                <flux:textarea wire:model="forward_to" name="forward_to" label="{{ __('Forward To (one email per line, optional)') }}" rows="4" />
                <flux:error name="forward_to_emails" />
                <flux:button type="submit">{{ __('Create Form') }}</flux:button>
            </form>
        </div>
    @endvolt
</x-layouts.app>
