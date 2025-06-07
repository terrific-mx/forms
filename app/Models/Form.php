<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Form extends Model
{
    use HasFactory;

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
        return Attribute::get(fn ($value, $attributes) =>
            empty($attributes['forward_to'] ?? null)
                ? []
                : array_filter(array_map('trim', preg_split('/\r?\n/', $attributes['forward_to']))));
    }

    protected function formattedEmbedded(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) =>
            '<form action="' . url('/f/' . $attributes['ulid']) . '" method="POST">'
        );
    }
}
