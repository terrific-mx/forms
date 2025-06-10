<?php

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

describe('basic form submission', function () {
    it('stores all post data in the data field and redirects', function () {
        $form = Form::factory()->create();
        $data = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $response = post("/f/{$form->ulid}", $data, [
            'HTTP_USER_AGENT' => 'TestAgent/1.0',
            'HTTP_REFERER' => 'https://example.com/ref',
            'REMOTE_ADDR' => '123.123.123.123',
        ]);
        $response->assertRedirect("/f/{$form->ulid}/thank-you");

        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'ip_address' => '123.123.123.123',
            'user_agent' => 'TestAgent/1.0',
            'referrer' => 'https://example.com/ref',
        ]);

        $submission = $form->submissions()->latest()->first();
        expect($submission->data)->toMatchArray([
            'field1' => 'value1',
            'field2' => 'value2',
        ]);
    });

    it('returns 404 for non-existent form', function () {
        $ulid = Str::ulid();
        $response = post("/f/{$ulid}", [
            'data' => ['foo' => 'bar'],
        ]);
        $response->assertNotFound();
    });

    it('sends notification to all emails in forward_to', function () {
        Notification::fake();
        $form = Form::factory()->create([
            'forward_to' => "one@example.com\ntwo@example.com",
        ]);
        $data = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        post("/f/{$form->ulid}", $data, [
            'HTTP_USER_AGENT' => 'TestAgent/1.0',
            'HTTP_REFERER' => 'https://example.com/ref',
            'REMOTE_ADDR' => '123.123.123.123',
        ]);

        Notification::assertSentOnDemandTimes(\App\Notifications\FormSubmissionReceived::class, 2);
    });
});

describe('redirect URL functionality', function () {
    it('redirects to custom URL when redirect_url is set', function () {
        $form = Form::factory()->create([
            'redirect_url' => 'https://example.com/custom-thank-you',
        ]);
        $data = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $response = post("/f/{$form->ulid}", $data);
        $response->assertRedirect('https://example.com/custom-thank-you');
    });

    it('redirects to default thank you page when redirect_url is empty', function () {
        $form = Form::factory()->create([
            'redirect_url' => '',
        ]);
        $data = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $response = post("/f/{$form->ulid}", $data);
        $response->assertRedirect("/f/{$form->ulid}/thank-you");
    });

    it('redirects to default thank you page when redirect_url is null', function () {
        $form = Form::factory()->create([
            'redirect_url' => null,
        ]);
        $data = [
            'field1' => 'value1',
            'field2' => 'value2',
        ];

        $response = post("/f/{$form->ulid}", $data);
        $response->assertRedirect("/f/{$form->ulid}/thank-you");
    });
});

describe('allowed domains functionality', function () {
    it('allows submissions when no allowed domains are configured', function ($allowedDomains) {
        $form = Form::factory()->create([
            'allowed_domains' => $allowedDomains,
        ]);
        $data = ['field1' => 'value1'];

        $response = post("/f/{$form->ulid}", $data, [
            'HTTP_REFERER' => 'https://any-domain.com/page',
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'referrer' => 'https://any-domain.com/page',
        ]);
    })->with([null, '']);

    it('allows submissions from allowed domains', function () {
        $form = Form::factory()->create([
            'allowed_domains' => 'example.com,mysite.org',
        ]);
        $data = ['field1' => 'value1'];

        $response = post("/f/{$form->ulid}", $data, [
            'HTTP_REFERER' => 'https://example.com/contact',
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'referrer' => 'https://example.com/contact',
        ]);
    });

    it('allows submissions from allowed subdomains', function () {
        $form = Form::factory()->create([
            'allowed_domains' => 'example.com',
        ]);
        $data = ['field1' => 'value1'];

        $response = post("/f/{$form->ulid}", $data, [
            'HTTP_REFERER' => 'https://blog.example.com/post',
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'referrer' => 'https://blog.example.com/post',
        ]);
    });

    it('rejects submissions from non-allowed domains', function () {
        $form = Form::factory()->create([
            'allowed_domains' => 'example.com,mysite.org',
        ]);
        $data = ['field1' => 'value1'];

        $response = post("/f/{$form->ulid}", $data, [
            'HTTP_REFERER' => 'https://malicious.com/form',
        ]);

        $response->assertStatus(403);
        assertDatabaseMissing('form_submissions', [
            'form_id' => $form->id,
            'referrer' => 'https://malicious.com/form',
        ]);
    });

    it('allows submissions when no referer header is present', function () {
        $form = Form::factory()->create([
            'allowed_domains' => 'example.com',
        ]);
        $data = ['field1' => 'value1'];

        $response = post("/f/{$form->ulid}", $data);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'referrer' => null,
        ]);
    });

    it('handles allowed domains with whitespace correctly', function () {
        $form = Form::factory()->create([
            'allowed_domains' => ' example.com , mysite.org , ',
        ]);
        $data = ['field1' => 'value1'];

        $response = post("/f/{$form->ulid}", $data, [
            'HTTP_REFERER' => 'https://mysite.org/contact',
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
            'referrer' => 'https://mysite.org/contact',
        ]);
    });
});

describe('honeypot protection', function () {
    it('allows submissions when honeypot field is not configured', function () {
        $form = Form::factory()->create([
            'honeypot_field' => null,
        ]);
        $data = [
            'field1' => 'value1',
            'secret_field' => 'bot_value', // This would normally be a honeypot
        ];

        $response = post("/f/{$form->ulid}", $data);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('allows submissions when honeypot field is empty', function () {
        $form = Form::factory()->create([
            'honeypot_field' => 'website',
        ]);
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'website' => '', // Honeypot field is empty (good)
        ];

        $response = post("/f/{$form->ulid}", $data);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('allows submissions when honeypot field is missing', function () {
        $form = Form::factory()->create([
            'honeypot_field' => 'website',
        ]);
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            // No honeypot field included
        ];

        $response = post("/f/{$form->ulid}", $data);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('rejects submissions when honeypot field has a value', function () {
        $form = Form::factory()->create([
            'honeypot_field' => 'website',
        ]);
        $data = [
            'name' => 'Bot Name',
            'email' => 'bot@example.com',
            'website' => 'http://spam.com', // Honeypot field has value (bad)
        ];

        $response = post("/f/{$form->ulid}", $data);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseMissing('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('trims honeypot field whitespace when checking', function () {
        $form = Form::factory()->create([
            'honeypot_field' => 'website',
        ]);
        $data = [
            'name' => 'Bot Name',
            'email' => 'bot@example.com',
            'website' => '   ', // Only whitespace should be treated as empty
        ];

        $response = post("/f/{$form->ulid}", $data);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('redirects to custom URL when honeypot is triggered and custom redirect is set', function () {
        $form = Form::factory()->create([
            'honeypot_field' => 'website',
            'redirect_url' => 'https://example.com/custom-thank-you',
        ]);
        $data = [
            'name' => 'Bot Name',
            'email' => 'bot@example.com',
            'website' => 'http://spam.com', // Honeypot field has value (bad)
        ];

        $response = post("/f/{$form->ulid}", $data);

        $response->assertRedirect('https://example.com/custom-thank-you');
        assertDatabaseMissing('form_submissions', [
            'form_id' => $form->id,
        ]);
    });
});

describe('Cloudflare Turnstile integration', function () {
    it('allows form submission when turnstile secret key is not configured', function () {
        $form = Form::factory()->create([
            'turnstile_secret_key' => null,
        ]);

        $response = post("/f/{$form->ulid}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        expect($form->submissions()->count())->toBe(1);
    });

    it('allows form submission when turnstile validation passes', function () {
        $form = Form::factory()->create([
            'turnstile_secret_key' => 'test-secret-key',
        ]);

        // Mock successful Turnstile verification
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $response = post("/f/{$form->ulid}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cf-turnstile-response' => 'valid-turnstile-token',
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        expect($form->submissions()->count())->toBe(1);

        // Verify the API was called with correct parameters
        Http::assertSent(function ($request) {
            return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify' &&
                   $request->data()['secret'] === 'test-secret-key' &&
                   $request->data()['response'] === 'valid-turnstile-token';
        });
    });

    it('rejects form submission when turnstile validation fails', function () {
        $form = Form::factory()->create([
            'turnstile_secret_key' => 'test-secret-key',
        ]);

        // Mock failed Turnstile verification
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ], 200),
        ]);

        $response = post("/f/{$form->ulid}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cf-turnstile-response' => 'invalid-turnstile-token',
        ]);

        $response->assertStatus(403);
        expect($form->submissions()->count())->toBe(0);
    });

    it('rejects form submission when turnstile response is missing and secret key is configured', function () {
        $form = Form::factory()->create([
            'turnstile_secret_key' => 'test-secret-key',
        ]);

        $response = post("/f/{$form->ulid}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            // Missing cf-turnstile-response
        ]);

        $response->assertStatus(403);
        expect($form->submissions()->count())->toBe(0);
    });

    it('handles turnstile API errors gracefully', function () {
        $form = Form::factory()->create([
            'turnstile_secret_key' => 'test-secret-key',
        ]);

        // Mock API error
        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response('', 500),
        ]);

        $response = post("/f/{$form->ulid}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'cf-turnstile-response' => 'some-token',
        ]);

        $response->assertStatus(403);
        expect($form->submissions()->count())->toBe(0);
    });
});

describe('blocked emails functionality', function () {
    it('allows submissions when no blocked emails are configured', function () {
        $form = Form::factory()->create();

        $response = post("/f/{$form->ulid}", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('allows submissions from non-blocked email addresses', function () {
        $form = Form::factory()->create();

        // Add some blocked emails
        $form->blockedEmails()->create(['email' => 'spam@example.com']);
        $form->blockedEmails()->create(['email' => 'troll@badsite.org']);

        $response = post("/f/{$form->ulid}", [
            'name' => 'John Doe',
            'email' => 'john@example.com', // Not in blocked list
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('rejects submissions from blocked email addresses', function () {
        $form = Form::factory()->create();

        // Add some blocked emails
        $form->blockedEmails()->create(['email' => 'spam@example.com']);
        $form->blockedEmails()->create(['email' => 'troll@badsite.org']);

        $response = post("/f/{$form->ulid}", [
            'name' => 'Spam Bot',
            'email' => 'spam@example.com', // This is in the blocked list
        ]);

        $response->assertStatus(403);
        assertDatabaseMissing('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('handles case-insensitive email blocking', function () {
        $form = Form::factory()->create();

        // Add blocked email in lowercase
        $form->blockedEmails()->create(['email' => 'spam@example.com']);

        $response = post("/f/{$form->ulid}", [
            'name' => 'Spam Bot',
            'email' => 'SPAM@EXAMPLE.COM', // Same email but uppercase
        ]);

        $response->assertStatus(403);
        assertDatabaseMissing('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('allows submissions when email field is missing', function () {
        $form = Form::factory()->create();

        // Add some blocked emails
        $form->blockedEmails()->create(['email' => 'spam@example.com']);

        $response = post("/f/{$form->ulid}", [
            'name' => 'John Doe',
            'message' => 'Hello world',
            // No email field provided
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('allows submissions when email field is empty', function () {
        $form = Form::factory()->create();

        // Add some blocked emails
        $form->blockedEmails()->create(['email' => 'spam@example.com']);

        $response = post("/f/{$form->ulid}", [
            'name' => 'John Doe',
            'email' => '', // Empty email field
            'message' => 'Hello world',
        ]);

        $response->assertRedirect("/f/{$form->ulid}/thank-you");
        assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    });

    it('blocks multiple email formats in form data', function () {
        $form = Form::factory()->create();

        // Add blocked email
        $form->blockedEmails()->create(['email' => 'spam@example.com']);

        // Test different field names that might contain emails
        $testCases = [
            ['email' => 'spam@example.com'],
            ['user_email' => 'spam@example.com'],
            ['contact_email' => 'spam@example.com'],
            ['from' => 'spam@example.com'],
        ];

        foreach ($testCases as $data) {
            $response = post("/f/{$form->ulid}", array_merge($data, ['name' => 'Test']));
            $response->assertStatus(403);
        }
    });
});
