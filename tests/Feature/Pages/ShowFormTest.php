<?php

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('redirects guests to the form page login', function () {
    $form = Form::factory()->create();
    get("/forms/{$form->id}")->assertRedirect('/login');
});

it('allows authenticated users to view the form submissions page', function () {
    $user = User::factory()->create();
    $form = Form::factory()->for($user)->create();
    actingAs($user)->get("/forms/{$form->id}")->assertOk();
});

it('shows submissions for the form', function () {
    $user = User::factory()->create();
    $form = Form::factory()->for($user)->create();
    $submission1 = FormSubmission::factory()->for($form)->create(['data' => ['name' => 'Alice']]);
    $submission2 = FormSubmission::factory()->for($form)->create(['data' => ['name' => 'Bob']]);

    $response = actingAs($user)->get("/forms/{$form->id}");
    $response->assertOk();
    $response->assertSee('Alice');
    $response->assertSee('Bob');
});

it('shows only submissions for the current form', function () {
    $user = User::factory()->create();
    $form = Form::factory()->for($user)->create();
    $otherForm = Form::factory()->for($user)->create();
    FormSubmission::factory()->for($form)->create(['data' => ['name' => 'Alice']]);
    FormSubmission::factory()->for($otherForm)->create(['data' => ['name' => 'Bob']]);

    $response = actingAs($user)->get("/forms/{$form->id}");
    $response->assertOk();
    $response->assertSee('Alice');
    $response->assertDontSee('Bob');
});
