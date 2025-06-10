<?php

use App\Models\Form;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

describe('form model behavior', function () {
    it('cleans up logo file when form is deleted', function () {
        Storage::fake('public');

        $user = User::factory()->create();

        // Create a form with a logo
        $file = UploadedFile::fake()->image('logo.png', 100, 100);
        $logoPath = $file->store('form-logos', 'public');

        $form = Form::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Form',
            'logo_path' => $logoPath,
        ]);

        // Verify logo exists
        expect(Storage::disk('public')->exists($logoPath))->toBeTrue();

        // Delete the form
        $form->delete();

        // Verify logo is cleaned up
        expect(Storage::disk('public')->exists($logoPath))->toBeFalse();
    });
});
