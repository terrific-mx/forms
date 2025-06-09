<?php

use App\Models\Form;
use Flux\Flux;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {
    public Form $form;
    public string $name = '';
    public string $forward_to = '';
    public array $forward_to_emails = [];

    public function mount()
    {
        $this->name = $this->form->name;
        $this->forward_to = $this->form->forward_to ?? '';
    }

    public function save()
    {
        $this->forward_to_emails = array_filter(array_map('trim', preg_split('/\r?\n/', $this->forward_to)));

        $this->validate([
            'name' => 'required|string|max:255',
            'forward_to' => 'nullable|string',
            'forward_to_emails.*' => 'sometimes|email',
        ]);

        $this->form->update([
            'name' => $this->name,
            'forward_to' => implode("\n", $this->forward_to_emails),
        ]);

        Flux::toast('Form settings updated successfully.');
    }
}; ?>

<x-layouts.app>
    @volt('pages.form.settings')
        <div class="w-full max-w-md mx-auto">
            <form wire:submit="save" class="grid grid-cols-1 gap-8">
                <div class="grid gap-2">
                    <flux:heading level="1" size="xl">{{ __('Form Settings') }}</flux:heading>
                    <flux:text>{{ __('Update your form name and email forwarding settings.') }}</flux:text>
                </div>

                <flux:input wire:model="name" name="name" :label="__('Form Name')" required />

                <div>
                    <flux:textarea
                        wire:model="forward_to"
                        name="forward_to"
                        :label="__('Forward To')"
                        :badge="__('Optional')"
                        :description:trailing="__('Enter one email address per line.')"
                        rows="4"
                    />
                    <flux:error name="forward_to_emails.*" />
                </div>

                <div class="flex max-sm:flex-col-reverse items-center max:sm:flex-col justify-end gap-3 max-sm:*:w-full">
                    <flux:button href="/forms/{{ $form->id }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Update Settings') }}</flux:button>
                </div>
            </form>
        </div>
    @endvolt
</x-layouts.app>
