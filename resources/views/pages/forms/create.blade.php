<?php

use Illuminate\Support\Facades\Auth;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

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
            'forward_to_emails.*' => 'sometimes|email',
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
            <form wire:submit="save" class="grid grid-cols-1 gap-8">
                <div class="grid gap-2">
                    <flux:heading level="1" size="xl">{{ __('Create New Form') }}</flux:heading>
                    <flux:text>{{ __('Create a new form to collect information from users.') }}</flux:text>
                </div>
                <flux:input wire:model="name" name="name" :label="__('Form Name')" required />
                <div>
                    <flux:textarea wire:model="forward_to" name="forward_to" :label="__('Forward To')" :badge="__('Optional')" :description:trailing="__('Enter one email address per line.')" rows="4" />
                    <flux:error name="forward_to_emails.*" />
                </div>
                <div class="flex max-sm:flex-col-reverse items-center max:sm:flex-col justify-end gap-3 max-sm:*:w-full">
                    @if(url()->previous() !== url()->current())
                        <flux:button :href="url()->previous()" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    @endif
                    <flux:button type="submit" variant="primary">{{ __('Create Form') }}</flux:button>
                </div>
            </form>
        </div>
    @endvolt
</x-layouts.app>
