<?php

use App\Models\Form;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('dashboard access', function () {
    it('redirects guests to the login page', function () {
        get('/dashboard')->assertRedirect('/login');
    });

    it('allows authenticated users to visit the dashboard', function () {
        $user = User::factory()->create();

        actingAs($user)->get('/dashboard')->assertOk();
    });
});

describe('dashboard display', function () {
    it('shows created forms on the dashboard', function () {
        $user = User::factory()->create();

        Form::factory()->for($user)->count(3)->sequence(
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

    it('shows only the authenticated user\'s forms on the dashboard', function () {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Forms for the authenticated user
        Form::factory()->for($user)->create(['name' => 'Contact Form']);
        Form::factory()->for($user)->create(['name' => 'Feedback Form']);
        // Form for another user
        Form::factory()->for($otherUser)->create(['name' => 'Other User Form']);

        $this->actingAs($user);
        $response = $this->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Contact Form');
        $response->assertSee('Feedback Form');
        $response->assertDontSee('Other User Form');
    });
});
