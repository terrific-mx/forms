<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedEmail extends Model
{
    protected $fillable = [
        'form_id',
        'email',
    ];

    protected static function boot()
    {
        parent::boot();

        // Convert email to lowercase before saving for consistent comparison
        static::saving(function ($model) {
            $model->email = strtolower(trim($model->email));
        });
    }

    /**
     * Get the form that owns the blocked email.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
