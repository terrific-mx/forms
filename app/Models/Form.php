<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Form extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (Form $form) {
            // Clean up logo file when form is deleted
            if ($form->logo_path && Storage::disk('public')->exists($form->logo_path)) {
                Storage::disk('public')->delete($form->logo_path);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }

    protected function forwardToEmails(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => empty($attributes['forward_to'] ?? null)
                ? []
                : array_filter(array_map('trim', preg_split('/\r?\n/', $attributes['forward_to']))));
    }

    protected function formattedEmbedded(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => '<form action="'.url('/f/'.$attributes['ulid']).'" method="POST">'
        );
    }

    protected function logoUrl(): Attribute
    {
        return Attribute::get(function () {
            if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
                return asset('storage/' . $this->logo_path);
            }
            return null;
        });
    }
}
