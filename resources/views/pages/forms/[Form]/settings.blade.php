<?php

use App\Models\Form;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

use function Laravel\Folio\middleware;

middleware(['auth', ValidateSessionWithWorkOS::class]);

new class extends Component {
    use WithFileUploads;

    public Form $form;
    public string $name = '';
    public string $forward_to = '';
    public string $redirect_url = '';
    public array $forward_to_emails = [];
    public $logo;

    public function mount()
    {
        $this->name = $this->form->name;
        $this->forward_to = $this->form->forward_to ?? '';
        $this->redirect_url = $this->form->redirect_url ?? '';
    }

    public function save()
    {
        $this->forward_to_emails = array_filter(array_map('trim', preg_split('/\r?\n/', $this->forward_to)));

        $this->validate([
            'name' => 'required|string|max:255',
            'forward_to' => 'nullable|string',
            'redirect_url' => 'nullable|url',
            'forward_to_emails.*' => 'sometimes|email',
            'logo' => 'nullable|image|max:2048', // Max 2MB
        ]);

        $updateData = [
            'name' => $this->name,
            'forward_to' => implode("\n", $this->forward_to_emails),
            'redirect_url' => $this->redirect_url,
        ];

        // Handle logo upload
        if ($this->logo) {
            // Delete old logo if exists
            if ($this->form->logo_path && Storage::disk('public')->exists($this->form->logo_path)) {
                Storage::disk('public')->delete($this->form->logo_path);
            }

            // Store new logo
            $logoPath = $this->logo->store('form-logos', 'public');
            $updateData['logo_path'] = $logoPath;
        }

        $this->form->update($updateData);

        Flux::toast('Form settings updated successfully.');
    }

    public function removeLogo()
    {
        if ($this->form->logo_path && Storage::disk('public')->exists($this->form->logo_path)) {
            Storage::disk('public')->delete($this->form->logo_path);
        }

        $this->form->update(['logo_path' => null]);
        Flux::toast('Logo removed successfully.');
    }
}; ?>

<x-layouts.app>
    @volt('pages.form.settings')
        <div class="w-full max-w-md mx-auto">
            <form wire:submit="save" class="grid grid-cols-1 gap-8">
                <div class="grid gap-2">
                    <flux:heading level="1" size="xl">{{ __('Form Settings') }}</flux:heading>
                    <flux:text>{{ __('Update your form name, email forwarding, and redirect settings.') }}</flux:text>
                </div>

                <flux:input wire:model="name" name="name" :label="__('Form Name')" required />

                <!-- Logo Upload Section -->
                <div class="space-y-3">
                    <div>
                        <flux:label>{{ __('Custom Logo') }}</flux:label>
                        <flux:badge variant="warning" size="sm">{{ __('Optional') }}</flux:badge>
                    </div>

                    @if($form->logo_url)
                        <div class="flex items-start gap-4">
                            <img src="{{ $form->logo_url }}" alt="Current logo" class="size-20 object-contain rounded-lg border">
                            <div class="flex-1 min-w-0">
                                <flux:text variant="strong">{{ __('Current Logo') }}</flux:text>
                                <flux:text size="sm">
                                    {{ __('Upload a new logo to replace this one.') }}
                                </flux:text>
                                <flux:button
                                    wire:click="removeLogo"
                                    size="sm"
                                    class="mt-2"
                                    wire:confirm="{{ __('Are you sure you want to remove the current logo?') }}"
                                >
                                    {{ __('Remove Logo') }}
                                </flux:button>
                            </div>
                        </div>
                    @endif

                    <div>
                        <flux:input
                            type="file"
                            wire:model="logo"
                            name="logo"
                            accept="image/*"
                            :description:trailing="__('Upload a logo image (JPG, PNG). Maximum size: 2MB.')"
                        />

                        @if($logo)
                            <div class="mt-2">
                                <flux:text color="green">
                                    {{ __('Ready to upload: ') }}{{ $logo->getClientOriginalName() }}
                                </flux:text>
                            </div>
                        @endif

                        <flux:error name="logo" />
                    </div>
                </div>

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

                <flux:input
                    wire:model="redirect_url"
                    name="redirect_url"
                    :label="__('Custom Redirect URL')"
                    :badge="__('Optional')"
                    :description:trailing="__('URL to redirect users after successful form submission. If left empty, users will see the default thank you page.')"
                    placeholder="https://example.com/thank-you"
                />

                <div class="flex max-sm:flex-col-reverse items-center max:sm:flex-col justify-end gap-3 max-sm:*:w-full">
                    <flux:button href="/forms/{{ $form->id }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Update Settings') }}</flux:button>
                </div>
            </form>
        </div>
    @endvolt
</x-layouts.app>
