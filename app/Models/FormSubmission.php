<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    use HasFactory;

    protected $casts = [
        'data' => 'array',
    ];

    protected $fillable = [
        'form_id', 'data', 'ip_address', 'user_agent', 'referrer', 'reply_email',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
