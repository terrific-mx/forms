<?php

use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('is marked as new when seen_at is null', function () {
    $form = Form::factory()->create();
    $submission = FormSubmission::factory()->for($form)->create(['seen_at' => null]);

    expect($submission->isNew())->toBeTrue();
    expect($submission->isSeen())->toBeFalse();
});

it('is marked as seen when seen_at has a value', function () {
    $form = Form::factory()->create();
    $submission = FormSubmission::factory()->for($form)->create(['seen_at' => now()]);

    expect($submission->isNew())->toBeFalse();
    expect($submission->isSeen())->toBeTrue();
});

it('can be marked as seen', function () {
    $form = Form::factory()->create();
    $submission = FormSubmission::factory()->for($form)->create(['seen_at' => null]);

    expect($submission->isNew())->toBeTrue();

    $submission->markAsSeen();
    $submission->refresh();

    expect($submission->isSeen())->toBeTrue();
    expect($submission->seen_at)->not()->toBeNull();
});

it('does not update seen_at if already seen', function () {
    $form = Form::factory()->create();
    $originalSeenAt = now()->subHour();
    $submission = FormSubmission::factory()->for($form)->create(['seen_at' => $originalSeenAt]);

    $submission->markAsSeen();
    $submission->refresh();

    expect($submission->seen_at->timestamp)->toBe($originalSeenAt->timestamp);
});

it('properly casts seen_at as datetime', function () {
    $form = Form::factory()->create();
    $submission = FormSubmission::factory()->for($form)->create(['seen_at' => now()]);

    expect($submission->seen_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('marks submission as seen when viewing submission page', function () {
    $user = User::factory()->create();
    $form = Form::factory()->for($user)->create();
    $submission = FormSubmission::factory()->for($form)->create(['seen_at' => null]);

    expect($submission->isNew())->toBeTrue();

    $this->actingAs($user)->get("/submissions/{$submission->id}");

    $submission->refresh();
    expect($submission->isSeen())->toBeTrue();
});

it('can filter new submissions using scope', function () {
    $form = Form::factory()->create();
    $newSubmission1 = FormSubmission::factory()->for($form)->create(['seen_at' => null]);
    $newSubmission2 = FormSubmission::factory()->for($form)->create(['seen_at' => null]);
    $seenSubmission = FormSubmission::factory()->for($form)->create(['seen_at' => now()]);

    $newSubmissions = FormSubmission::new()->get();

    expect($newSubmissions)->toHaveCount(2);
    expect($newSubmissions->pluck('id'))->toContain($newSubmission1->id, $newSubmission2->id);
    expect($newSubmissions->pluck('id'))->not()->toContain($seenSubmission->id);
});

it('can filter seen submissions using scope', function () {
    $form = Form::factory()->create();
    $newSubmission = FormSubmission::factory()->for($form)->create(['seen_at' => null]);
    $seenSubmission1 = FormSubmission::factory()->for($form)->create(['seen_at' => now()->subHour()]);
    $seenSubmission2 = FormSubmission::factory()->for($form)->create(['seen_at' => now()->subMinute()]);

    $seenSubmissions = FormSubmission::seen()->get();

    expect($seenSubmissions)->toHaveCount(2);
    expect($seenSubmissions->pluck('id'))->toContain($seenSubmission1->id, $seenSubmission2->id);
    expect($seenSubmissions->pluck('id'))->not()->toContain($newSubmission->id);
});

it('shows new submission counts on dashboard', function () {
    $user = User::factory()->create();
    $form = Form::factory()->for($user)->create();

    // Create some new and seen submissions
    FormSubmission::factory()->for($form)->count(3)->create(['seen_at' => null]);
    FormSubmission::factory()->for($form)->count(2)->create(['seen_at' => now()->subHour()]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('3 new'); // Should show 3 new submissions
});

it('shows submission is seen after viewing', function () {
    $user = User::factory()->create();
    $form = Form::factory()->for($user)->create();
    $submission = FormSubmission::factory()->for($form)->create(['seen_at' => null]);

    // Before viewing - should be new
    expect($submission->isNew())->toBeTrue();

    // View the submission
    $response = $this->actingAs($user)->get("/submissions/{$submission->id}");
    $response->assertOk();

    // After viewing - should be seen
    $submission->refresh();
    expect($submission->isSeen())->toBeTrue();
});
