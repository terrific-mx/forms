<?php

use App\Models\Form;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('can update a form', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create([
        'user_id' => $user->id,
        'name' => 'Original Form',
        'forward_to' => "old@example.com",
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

it('can update form with empty forward_to field', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Form',
        'forward_to' => "old@example.com",
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

it('shows success message after updating form', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Form',
    ]);

    Volt::actingAs($user)->test('pages.form.settings', ['form' => $form])
        ->set('name', 'Updated Form')
        ->call('save')
        ->assertSessionHas('message', 'Form settings updated successfully.');
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
