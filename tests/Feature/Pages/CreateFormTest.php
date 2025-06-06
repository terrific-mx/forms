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
        ->call('save')
        ->assertRedirect('dashboard');

    assertDatabaseHas('forms', [
        'name' => 'Test Form',
        'user_id' => $user->id,
    ]);
});
