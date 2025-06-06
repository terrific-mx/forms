<?php

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    \Illuminate\Support\Facades\Notification::fake();
    $form = Form::factory()->create([
        'forward_to' => "one@example.com, two@example.com;three@example.com\nfour@example.com"
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

    $submission = $form->submissions()->latest()->first();
    $emails = ['one@example.com', 'two@example.com', 'three@example.com', 'four@example.com'];
    foreach ($emails as $email) {
        \Illuminate\Support\Facades\Notification::assertSentTo(
            new \Illuminate\Notifications\AnonymousNotifiable,
            \App\Notifications\FormSubmissionReceived::class,
            function ($notification, $channels, $notifiable) use ($email, $form, $submission) {
                return $notifiable->routes['mail'] === $email
                    && $notification->form->id === $form->id
                    && $notification->submission->id === $submission->id;
            }
        );
    }
});
