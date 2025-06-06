<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('can create a form', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)->test('pages.forms.create')
        ->set('name', 'Test Form')
        ->set('forward_to', "one@example.com\ntwo@example.com")
        ->call('save')
        ->assertRedirect('dashboard');

    assertDatabaseHas('forms', [
        'name' => 'Test Form',
        'user_id' => $user->id,
        'forward_to' => "one@example.com\ntwo@example.com",
    ]);
});

it('cannot create a form without a name', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)->test('pages.forms.create')
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

it('cannot create a form with a name longer than 255 characters', function () {
    $user = User::factory()->create();
    $longName = str_repeat('a', 256);

    Volt::actingAs($user)->test('pages.forms.create')
        ->set('name', $longName)
        ->call('save')
        ->assertHasErrors(['name' => 'max']);
});

it('can create a form with an empty forward_to field', function () {
    $user = User::factory()->create();

    Volt::actingAs($user)->test('pages.forms.create')
        ->set('name', 'No Forward')
        ->set('forward_to', '')
        ->call('save')
        ->assertRedirect('dashboard');

    assertDatabaseHas('forms', [
        'name' => 'No Forward',
        'user_id' => $user->id,
        'forward_to' => '',
    ]);
});
