<?php

use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('displays the thank you page after form submission', function () {
    $form = Form::factory()->create();

    $response = get("/f/{$form->ulid}/thank-you");

    $response->assertStatus(200);
    $response->assertSee('Thanks for your submission!');
});

it('displays custom logo on thank you page when form has logo', function () {
    Storage::fake('public');

    // Create a logo file
    $file = UploadedFile::fake()->image('logo.png', 100, 100);
    $logoPath = $file->store('form-logos', 'public');

    $form = Form::factory()->create([
        'name' => 'Test Form with Logo',
        'logo_path' => $logoPath,
    ]);

    $response = get("/f/{$form->ulid}/thank-you");

    $response->assertStatus(200);
    $response->assertSee('Thanks for your submission!');
    $response->assertSee('Test Form with Logo');
    $response->assertSee(asset('storage/'.$logoPath));
});

it('displays default app brand on thank you page when form has no logo', function () {
    $form = Form::factory()->create([
        'name' => 'Test Form without Logo',
        'logo_path' => null,
    ]);

    $response = get("/f/{$form->ulid}/thank-you");

    $response->assertStatus(200);
    $response->assertSee('Thanks for your submission!');
    // Should not show custom logo elements - form name only appears when there's a custom logo
    $response->assertDontSee('Test Form without Logo');
});
