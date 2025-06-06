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
        '_token' => 'sometoken', // Simulate CSRF token
    ];

    $response = $this->post("/f/{$form->ulid}", $data);
    $response->assertRedirect("/f/{$form->ulid}/thank-you");

    $this->assertDatabaseHas('form_submissions', [
        'form_id' => $form->id,
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
