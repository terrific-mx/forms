<?php

use App\Models\User;
use App\Models\Form;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

it('redirects guests to the login page', function () {
    get('/dashboard')->assertRedirect('/login');
});

it('allows authenticated users to visit the dashboard', function () {
    $user = User::factory()->create();

    actingAs($user)->get('/dashboard')->assertOk();
});

it('shows created forms on the dashboard', function () {
    $user = User::factory()->create();

    Form::factory()->count(3)->sequence(
        ['name' => 'Contact Form'],
        ['name' => 'Feedback Form'],
        ['name' => 'Registration Form'],
    )->create();

    $response = actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Contact Form');
    $response->assertSee('Feedback Form');
    $response->assertSee('Registration Form');
});
