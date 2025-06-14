<?php

use App\Models\Form;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

describe('form updates', function () {
    it('can update a form', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Original Form',
            'forward_to' => 'old@example.com',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Updated Form')
            ->set('forward_to', "new@example.com\nsecond@example.com")
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'name' => 'Updated Form',
            'user_id' => $user->id,
            'forward_to' => "new@example.com\nsecond@example.com",
        ]);
    });

    it('initializes form fields with existing data', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'forward_to' => "test@example.com\nsecond@example.com",
        ]);

        $component = Volt::actingAs($user)->test('pages.form.settings', ['form' => $form]);

        expect($component->get('name'))->toBe('Test Form');
        expect($component->get('forward_to'))->toBe("test@example.com\nsecond@example.com");
    });
});

describe('form name validation', function () {
    it('cannot update form without a name', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);
    });

    it('cannot update form with a name longer than 255 characters', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);
        $longName = str_repeat('a', 256);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', $longName)
            ->call('save')
            ->assertHasErrors(['name' => 'max']);
    });
});

describe('forward to email handling', function () {
    it('can update form with empty forward_to field', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'forward_to' => 'old@example.com',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Updated Form')
            ->set('forward_to', '')
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'name' => 'Updated Form',
            'forward_to' => '',
        ]);
    });

    it('cannot update form with invalid emails in forward_to', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Updated Form')
            ->set('forward_to', "valid@example.com\ninvalid-email\nother@example.com")
            ->call('save')
            ->assertHasErrors(['forward_to_emails.1' => 'email']);
    });

    it('handles form with null forward_to field', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'forward_to' => null,
        ]);

        $component = Volt::actingAs($user)->test('pages.form.settings', ['form' => $form]);

        expect($component->get('forward_to'))->toBe('');
    });

    it('trims whitespace from email addresses', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Updated Form')
            ->set('forward_to', "  one@example.com  \n  two@example.com  ")
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'forward_to' => "one@example.com\ntwo@example.com",
        ]);
    });

    it('filters out empty lines from forward_to emails', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Updated Form')
            ->set('forward_to', "one@example.com\n\n\ntwo@example.com\n")
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'forward_to' => "one@example.com\ntwo@example.com",
        ]);
    });
});

describe('redirect URL functionality', function () {
    it('can update form with redirect URL', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'redirect_url' => null,
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Test Form')
            ->set('redirect_url', 'https://example.com/custom-thank-you')
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'redirect_url' => 'https://example.com/custom-thank-you',
        ]);
    });

    it('initializes redirect URL field with existing data', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'redirect_url' => 'https://example.com/existing-redirect',
        ]);

        $component = Volt::actingAs($user)->test('pages.form.settings', ['form' => $form]);

        expect($component->get('redirect_url'))->toBe('https://example.com/existing-redirect');
    });

    it('can clear redirect URL field', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'redirect_url' => 'https://example.com/old-redirect',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Test Form')
            ->set('redirect_url', '')
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'redirect_url' => '',
        ]);
    });

    it('validates redirect URL format', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Test Form')
            ->set('redirect_url', 'invalid-url')
            ->call('save')
            ->assertHasErrors(['redirect_url' => 'url']);
    });
});

describe('allowed domains functionality', function () {
    it('can update form with allowed domains', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'allowed_domains' => null,
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Test Form')
            ->set('allowed_domains', 'example.com, mysite.org')
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'allowed_domains' => 'example.com, mysite.org',
        ]);
    });

    it('can clear allowed domains', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'allowed_domains' => 'example.com',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Test Form')
            ->set('allowed_domains', '')
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'allowed_domains' => '',
        ]);
    });

    it('initializes allowed_domains field correctly', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'allowed_domains' => 'example.com, mysite.org',
        ]);

        $component = Volt::actingAs($user)->test('pages.form.settings', ['form' => $form]);

        expect($component->get('allowed_domains'))->toBe('example.com, mysite.org');
    });
});

describe('honeypot field functionality', function () {
    it('can configure honeypot field', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'honeypot_field' => null,
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Test Form')
            ->set('honeypot_field', 'website')
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'honeypot_field' => 'website',
        ]);
    });

    it('can clear honeypot field', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'honeypot_field' => 'website',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Test Form')
            ->set('honeypot_field', '')
            ->call('save');

        assertDatabaseHas('forms', [
            'id' => $form->id,
            'honeypot_field' => null,
        ]);
    });

    it('initializes honeypot_field correctly', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'honeypot_field' => 'website',
        ]);

        $component = Volt::actingAs($user)->test('pages.form.settings', ['form' => $form]);

        expect($component->get('honeypot_field'))->toBe('website');
    });
});

describe('logo upload functionality', function () {
    it('can upload and update form logo', function () {
        Storage::fake('public');

        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        $file = UploadedFile::fake()->image('logo.png', 100, 100);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Test Form')
            ->set('logo', $file)
            ->call('save');

        $form->refresh();

        expect($form->logo_path)->not->toBeNull();
        expect(Storage::disk('public')->exists($form->logo_path))->toBeTrue();
    });

    it('can remove form logo', function () {
        Storage::fake('public');

        $user = User::factory()->create();

        // Create a form with a logo
        $file = UploadedFile::fake()->image('logo.png', 100, 100);
        $logoPath = $file->store('form-logos', 'public');

        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'logo_path' => $logoPath,
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->call('removeLogo');

        $form->refresh();

        expect($form->logo_path)->toBeNull();
        expect(Storage::disk('public')->exists($logoPath))->toBeFalse();
    });
});

describe('authentication', function () {
    it('requires authentication to access form settings', function () {
        $form = Form::factory()->create();

        get("/forms/{$form->id}/settings")
            ->assertRedirect('/login');
    });
});

describe('turnstile secret key functionality', function () {
    it('can update turnstile secret key', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('turnstile_secret_key', '0x4AAAAAAABkMYinukE_NJBz...')
            ->call('save');

        $form->refresh();
        expect($form->turnstile_secret_key)->toBe('0x4AAAAAAABkMYinukE_NJBz...');
    });

    it('can clear turnstile secret key', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'turnstile_secret_key' => 'existing-key',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->call('clearTurnstileKey');

        $form->refresh();
        expect($form->turnstile_secret_key)->toBeNull();
    });

    it('preserves existing turnstile key when field is left empty', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'turnstile_secret_key' => 'existing-key',
        ]);

        // Save without changing the turnstile key (field left empty)
        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('name', 'Updated Form')
            ->call('save');

        $form->refresh();
        expect($form->turnstile_secret_key)->toBe('existing-key');
    });

    it('initializes turnstile_secret_key field correctly', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'turnstile_secret_key' => 'test-secret-key',
        ]);

        $component = Volt::actingAs($user)->test('pages.form.settings', ['form' => $form]);

        // Should not prefill encrypted key for security
        expect($component->get('turnstile_secret_key'))->toBe('');
    });

    it('can read existing encrypted turnstile secret keys', function () {
        $user = User::factory()->create();
        $secretKey = 'test-secret-key-123';

        // Create a form with encrypted key
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'turnstile_secret_key' => $secretKey,
        ]);

        // Save and reload to ensure it goes through the encryption/decryption cycle
        $formId = $form->id;
        $reloadedForm = Form::find($formId);

        expect($reloadedForm->turnstile_secret_key)->toBe($secretKey);
    });
});

describe('blocked emails functionality', function () {
    it('can add a single blocked email to a form', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('new_blocked_email', 'spam@example.com')
            ->call('addBlockedEmail');

        $form->refresh();
        expect($form->blockedEmails()->count())->toBe(1);
        expect($form->blockedEmails()->first()->email)->toBe('spam@example.com');
    });

    it('can add multiple blocked emails one by one', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        $component = Volt::actingAs($user)->test('pages.form.settings', ['form' => $form]);

        $component->set('new_blocked_email', 'spam@example.com')
            ->call('addBlockedEmail')
            ->set('new_blocked_email', 'troll@badsite.org')
            ->call('addBlockedEmail');

        $form->refresh();
        expect($form->blockedEmails()->count())->toBe(2);
        expect($form->blockedEmails()->pluck('email')->toArray())
            ->toMatchArray(['spam@example.com', 'troll@badsite.org']);
    });

    it('can remove a specific blocked email from a form', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        // Add some blocked emails first
        $blockedEmail1 = $form->blockedEmails()->create(['email' => 'spam@example.com']);
        $blockedEmail2 = $form->blockedEmails()->create(['email' => 'troll@badsite.org']);

        expect($form->blockedEmails()->count())->toBe(2);

        // Remove one specific email
        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->call('removeBlockedEmail', $blockedEmail1->id);

        $form->refresh();
        expect($form->blockedEmails()->count())->toBe(1);
        expect($form->blockedEmails()->first()->email)->toBe('troll@badsite.org');
    });

    it('validates blocked email address format', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('new_blocked_email', 'invalid-email')
            ->call('addBlockedEmail')
            ->assertHasErrors(['new_blocked_email' => 'email']);
    });

    it('requires blocked email address to be provided', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('new_blocked_email', '')
            ->call('addBlockedEmail')
            ->assertHasErrors(['new_blocked_email' => 'required']);
    });

    it('prevents duplicate blocked emails', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        // Add first email
        $form->blockedEmails()->create(['email' => 'spam@example.com']);

        // Try to add the same email again
        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('new_blocked_email', 'spam@example.com')
            ->call('addBlockedEmail')
            ->assertHasErrors(['new_blocked_email' => 'unique']);

        $form->refresh();
        expect($form->blockedEmails()->count())->toBe(1);
    });

    it('handles case-insensitive email blocking', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        // Add email in lowercase
        $form->blockedEmails()->create(['email' => 'spam@example.com']);

        // Try to add the same email in uppercase
        Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('new_blocked_email', 'SPAM@EXAMPLE.COM')
            ->call('addBlockedEmail')
            ->assertHasErrors(['new_blocked_email' => 'lowercase']);

        $form->refresh();
        expect($form->blockedEmails()->count())->toBe(1);
    });

    it('displays existing blocked emails in the form', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        $form->blockedEmails()->create(['email' => 'spam@example.com']);
        $form->blockedEmails()->create(['email' => 'troll@badsite.org']);

        $component = Volt::actingAs($user)->test('pages.form.settings', ['form' => $form]);

        // The blocked emails should be loaded as a relationship
        expect($form->blockedEmails()->count())->toBe(2);
    });

    it('clears the input field after adding a blocked email', function () {
        $user = User::factory()->create();
        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
        ]);

        $component = Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
            ->set('new_blocked_email', 'spam@example.com')
            ->call('addBlockedEmail');

        // The input should be cleared after adding
        expect($component->get('new_blocked_email'))->toBe('');
    });
});
