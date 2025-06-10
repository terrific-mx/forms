<?php

use App\Models\User;
use Livewire\Volt\Volt;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('profile page access', function () {
    test('profile page is displayed', function () {
        $this->actingAs($user = User::factory()->create());

        $this->get('/settings/profile')->assertOk();
    });
});

describe('profile management', function () {
    test('profile information can be updated', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.profile')
            ->set('name', 'Test User')
            ->call('updateProfileInformation');

        $response->assertHasNoErrors();

        $user->refresh();

        expect($user->name)->toEqual('Test User');
    });
});

describe('account deletion', function () {
    test('user can delete their account', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = Volt::test('settings.delete-user-form')
            ->call('deleteUser');

        $response->assertRedirect('/');

        expect($user->fresh())->toBeNull();
    });
});
