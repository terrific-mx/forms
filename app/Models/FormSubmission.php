<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::get(fn () => $this->created_at->format('M j, Y \\a\\t g:i A'));
    }
}
