<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function getForwardToEmailsAttribute()
    {
        if (empty($this->forward_to)) {
            return [];
        }
        return array_filter(array_map('trim', preg_split('/\r?\n/', $this->forward_to)));
    }
}
