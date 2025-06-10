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
    public string $allowed_domains = '';
    public string $honeypot_field = '';
    public string $turnstile_secret_key = '';
    public array $forward_to_emails = [];
    public $logo;
    public string $new_blocked_email = '';

    public function mount()
    {
        $this->name = $this->form->name;
        $this->forward_to = $this->form->forward_to ?? '';
        $this->redirect_url = $this->form->redirect_url ?? '';
        $this->allowed_domains = $this->form->allowed_domains ?? '';
        $this->honeypot_field = $this->form->honeypot_field ?? '';
        // Don't prefill encrypted secret key for security
        $this->turnstile_secret_key = '';
    }

    public function save()
    {
        $this->forward_to_emails = array_filter(array_map('trim', preg_split('/\r?\n/', $this->forward_to)));

        $this->validate([
            'name' => 'required|string|max:255',
            'forward_to' => 'nullable|string',
            'redirect_url' => 'nullable|url',
            'allowed_domains' => 'nullable|string',
            'honeypot_field' => 'nullable|string|max:255',
            'turnstile_secret_key' => 'nullable|string|max:255',
            'forward_to_emails.*' => 'sometimes|email',
            'logo' => 'nullable|image|max:2048',
            'new_blocked_email' => 'nullable|email|max:255',
        ]);

        $updateData = [
            'name' => $this->name,
            'forward_to' => implode("\n", $this->forward_to_emails),
            'redirect_url' => $this->redirect_url,
            'allowed_domains' => $this->allowed_domains,
            'honeypot_field' => $this->honeypot_field ?: null,
        ];

        // Only update turnstile_secret_key if a value was provided
        // This allows keeping the existing encrypted key when field is left empty
        if ($this->turnstile_secret_key !== '') {
            $updateData['turnstile_secret_key'] = $this->turnstile_secret_key;
        }

        if ($this->logo) {
            $updateData['logo_path'] = $this->handleLogoUpload();
        }

        $this->form->update($updateData);
        $this->reset('logo', 'turnstile_secret_key');
        Flux::toast('Form settings updated successfully.');
    }

    private function handleLogoUpload(): string
    {
        // Delete old logo if exists
        $this->deleteCurrentLogo();

        // Store new logo
        return $this->logo->store('form-logos', 'public');
    }

    private function deleteCurrentLogo(): void
    {
        if ($this->form->logo_path && Storage::disk('public')->exists($this->form->logo_path)) {
            Storage::disk('public')->delete($this->form->logo_path);
        }
    }

    public function removeLogo()
    {
        $this->deleteCurrentLogo();
        $this->form->update(['logo_path' => null]);
        Flux::toast('Logo removed successfully.');
    }

    public function clearTurnstileKey()
    {
        $this->form->update(['turnstile_secret_key' => null]);
        Flux::toast('Turnstile secret key removed successfully.');
    }

    public function addBlockedEmail()
    {
        $this->validate([
            'new_blocked_email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Check for duplicate (case-insensitive)
                    $exists = $this->form->blockedEmails()
                        ->whereRaw('LOWER(email) = ?', [strtolower(trim($value))])
                        ->exists();
                    
                    if ($exists) {
                        $fail('This email address is already blocked.');
                    }
                },
            ],
        ]);

        $this->form->blockedEmails()->create([
            'email' => trim($this->new_blocked_email),
        ]);

        $this->reset('new_blocked_email');
        Flux::toast('Email address added to blocked list.');
    }

    public function removeBlockedEmail($blockedEmailId)
    {
        $this->form->blockedEmails()->where('id', $blockedEmailId)->delete();
        Flux::toast('Email address removed from blocked list.');
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
                        <div class="flex items-center gap-4">
                            <flux:avatar class="[:where(&)]:size-20" :src="$form->logo_url" />
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
                        <flux:error name="logo" />

                        @if($logo)
                            <div class="mt-2">
                                <flux:text color="green">
                                    {{ __('Ready to upload: ') }}{{ $logo->getClientOriginalName() }}
                                </flux:text>
                            </div>
                        @endif
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

                <flux:input
                    wire:model="allowed_domains"
                    name="allowed_domains"
                    :label="__('Allowed Domains')"
                    :badge="__('Optional')"
                    :description:trailing="__('Comma-separated list of domains that can submit to this form (e.g., example.com, mysite.org). If left empty, submissions from any domain will be accepted.')"
                    placeholder="example.com, mysite.org"
                />

                <flux:input
                    wire:model="honeypot_field"
                    name="honeypot_field"
                    :label="__('Honeypot Field Name')"
                    :badge="__('Optional')"
                    :description:trailing="__('Name of a hidden field to catch spam bots. If a submission includes this field with a value, it will be rejected. Leave empty to disable spam protection.')"
                    placeholder="website"
                />

                <div class="space-y-3">
                    <div>
                        <flux:label>{{ __('Cloudflare Turnstile Secret Key') }}</flux:label>
                        <flux:badge variant="warning" size="sm">{{ __('Optional') }}</flux:badge>
                    </div>

                    @if($form->turnstile_secret_key)
                        <flux:callout icon="shield-check" variant="secondary">
                            <flux:callout.heading>{{ __('Turnstile Protection Active') }}</flux:callout.heading>
                            <flux:callout.text>{{ __('A Turnstile secret key is currently configured and protecting your form from spam. Enter a new key to replace it or leave the field empty to keep the current key.') }}</flux:callout.text>
                            <x-slot name="actions">
                                <flux:button
                                    wire:click="clearTurnstileKey"
                                    size="sm"
                                    wire:confirm="{{ __('Are you sure you want to remove the current Turnstile secret key?') }}"
                                >
                                    {{ __('Remove Protection') }}
                                </flux:button>
                            </x-slot>
                        </flux:callout>
                    @endif

                    <flux:input
                        wire:model="turnstile_secret_key"
                        name="turnstile_secret_key"
                        type="password"
                        :placeholder="$form->turnstile_secret_key ? __('Enter new key to replace existing') : __('0x4AAAAAAABkMYinukE_NJBz...')"
                        :description:trailing="__('Secret key for Cloudflare Turnstile verification. If provided, all form submissions must include a valid Turnstile token. Leave empty to disable Turnstile protection.')"
                    />
                </div>

                <!-- Blocked Emails Section -->
                <div class="space-y-3">
                    <div>
                        <flux:label>{{ __('Blocked Email Addresses') }}</flux:label>
                        <flux:badge variant="warning" size="sm">{{ __('Optional') }}</flux:badge>
                    </div>

                    <!-- Existing blocked emails list -->
                    @if($form->blockedEmails->isNotEmpty())
                        <div class="space-y-2">
                            <flux:text size="sm">{{ __('Currently blocked email addresses:') }}</flux:text>
                            @foreach($form->blockedEmails as $blockedEmail)
                                <flux:card class="p-3">
                                    <div class="flex items-center justify-between">
                                        <flux:text class="font-mono text-sm">{{ $blockedEmail->email }}</flux:text>
                                        <flux:button
                                            wire:click="removeBlockedEmail({{ $blockedEmail->id }})"
                                            size="sm"
                                            variant="ghost"
                                            color="red"
                                            wire:confirm="{{ __('Are you sure you want to remove this blocked email address?') }}"
                                        >
                                            {{ __('Remove') }}
                                        </flux:button>
                                    </div>
                                </flux:card>
                            @endforeach
                        </div>
                    @endif

                    <!-- Add new blocked email -->
                    <div class="flex gap-2">
                        <div class="flex-1">
                            <flux:input
                                wire:model="new_blocked_email"
                                name="new_blocked_email"
                                type="email"
                                placeholder="spam@example.com"
                                :description:trailing="__('Enter an email address to block from submitting this form.')"
                            />
                            <flux:error name="new_blocked_email" />
                        </div>
                        <flux:button
                            wire:click="addBlockedEmail"
                            variant="primary"
                            :disabled="empty($new_blocked_email)"
                        >
                            {{ __('Block Email') }}
                        </flux:button>
                    </div>
                </div>

                <div class="flex max-sm:flex-col-reverse items-center max:sm:flex-col justify-end gap-3 max-sm:*:w-full">
                    <flux:button href="/forms/{{ $form->id }}" variant="ghost" wire:navigate>{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Update Settings') }}</flux:button>
                </div>
            </form>
        </div>
    @endvolt
</x-layouts.app>
