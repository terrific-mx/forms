<?php

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('displays the thank you page after form submission', function () {
    $form = Form::factory()->create();

    $response = get("/f/{$form->ulid}/thank-you");

    $response->assertStatus(200);
    $response->assertSee('Thank you for submitting the form!');
});
