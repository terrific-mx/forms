<?php

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('stores all post data in the data field and redirects', function () {
    $form = Form::factory()->create();
    $data = [
        'field1' => 'value1',
        'field2' => 'value2',
    ];

    $response = $this->post("/f/{$form->ulid}", $data, [
        'HTTP_USER_AGENT' => 'TestAgent/1.0',
        'HTTP_REFERER' => 'https://example.com/ref',
        'REMOTE_ADDR' => '123.123.123.123',
    ]);
    $response->assertRedirect("/f/{$form->ulid}/thank-you");

    $this->assertDatabaseHas('form_submissions', [
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
    $response = $this->post("/f/{$ulid}", [
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

    $this->post("/f/{$form->ulid}", $data, [
        'HTTP_USER_AGENT' => 'TestAgent/1.0',
        'HTTP_REFERER' => 'https://example.com/ref',
        'REMOTE_ADDR' => '123.123.123.123',
    ]);

    Notification::assertSentOnDemandTimes(\App\Notifications\FormSubmissionReceived::class, 2);
});

it('redirects to custom URL when redirect_url is set', function () {
    $form = Form::factory()->create([
        'redirect_url' => 'https://example.com/custom-thank-you',
    ]);
    $data = [
        'field1' => 'value1',
        'field2' => 'value2',
    ];

    $response = $this->post("/f/{$form->ulid}", $data);
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

    $response = $this->post("/f/{$form->ulid}", $data);
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

    $response = $this->post("/f/{$form->ulid}", $data);
    $response->assertRedirect("/f/{$form->ulid}/thank-you");
});

it('allows submissions when no allowed domains are configured', function ($allowedDomains) {
    $form = Form::factory()->create([
        'allowed_domains' => $allowedDomains,
    ]);
    $data = ['field1' => 'value1'];

    $response = $this->post("/f/{$form->ulid}", $data, [
        'HTTP_REFERER' => 'https://any-domain.com/page',
    ]);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseHas('form_submissions', [
        'form_id' => $form->id,
        'referrer' => 'https://any-domain.com/page',
    ]);
})->with([null, '']);

it('allows submissions from allowed domains', function () {
    $form = Form::factory()->create([
        'allowed_domains' => 'example.com,mysite.org',
    ]);
    $data = ['field1' => 'value1'];

    $response = $this->post("/f/{$form->ulid}", $data, [
        'HTTP_REFERER' => 'https://example.com/contact',
    ]);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseHas('form_submissions', [
        'form_id' => $form->id,
        'referrer' => 'https://example.com/contact',
    ]);
});

it('allows submissions from allowed subdomains', function () {
    $form = Form::factory()->create([
        'allowed_domains' => 'example.com',
    ]);
    $data = ['field1' => 'value1'];

    $response = $this->post("/f/{$form->ulid}", $data, [
        'HTTP_REFERER' => 'https://blog.example.com/post',
    ]);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseHas('form_submissions', [
        'form_id' => $form->id,
        'referrer' => 'https://blog.example.com/post',
    ]);
});

it('rejects submissions from non-allowed domains', function () {
    $form = Form::factory()->create([
        'allowed_domains' => 'example.com,mysite.org',
    ]);
    $data = ['field1' => 'value1'];

    $response = $this->post("/f/{$form->ulid}", $data, [
        'HTTP_REFERER' => 'https://malicious.com/form',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('form_submissions', [
        'form_id' => $form->id,
        'referrer' => 'https://malicious.com/form',
    ]);
});

it('allows submissions when no referer header is present', function () {
    $form = Form::factory()->create([
        'allowed_domains' => 'example.com',
    ]);
    $data = ['field1' => 'value1'];

    $response = $this->post("/f/{$form->ulid}", $data);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseHas('form_submissions', [
        'form_id' => $form->id,
        'referrer' => null,
    ]);
});

it('handles allowed domains with whitespace correctly', function () {
    $form = Form::factory()->create([
        'allowed_domains' => ' example.com , mysite.org , ',
    ]);
    $data = ['field1' => 'value1'];

    $response = $this->post("/f/{$form->ulid}", $data, [
        'HTTP_REFERER' => 'https://mysite.org/contact',
    ]);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseHas('form_submissions', [
        'form_id' => $form->id,
        'referrer' => 'https://mysite.org/contact',
    ]);
});

it('allows submissions when honeypot field is not configured', function () {
    $form = Form::factory()->create([
        'honeypot_field' => null,
    ]);
    $data = [
        'field1' => 'value1',
        'secret_field' => 'bot_value', // This would normally be a honeypot
    ];

    $response = $this->post("/f/{$form->ulid}", $data);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseHas('form_submissions', [
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

    $response = $this->post("/f/{$form->ulid}", $data);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseHas('form_submissions', [
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

    $response = $this->post("/f/{$form->ulid}", $data);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseHas('form_submissions', [
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

    $response = $this->post("/f/{$form->ulid}", $data);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseMissing('form_submissions', [
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

    $response = $this->post("/f/{$form->ulid}", $data);

    $response->assertRedirect("/f/{$form->ulid}/thank-you");
    $this->assertDatabaseHas('form_submissions', [
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

    $response = $this->post("/f/{$form->ulid}", $data);

    $response->assertRedirect('https://example.com/custom-thank-you');
    $this->assertDatabaseMissing('form_submissions', [
        'form_id' => $form->id,
    ]);
});
