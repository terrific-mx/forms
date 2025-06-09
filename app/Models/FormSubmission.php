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
        'seen_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    protected function formattedCreatedAt(): Attribute
    {
        return Attribute::get(fn () => $this->created_at?->format('M j, Y \\a\\t g:i A'));
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            // Example: use email from data if available, fallback to a default avatar
            $email = $this->data['email'] ?? null;
            if ($email) {
                return 'https://unavatar.io/'.urlencode($email);
            }

            return 'https://unavatar.io/'.config('app.url');
        });
    }

    protected function email(): Attribute
    {
        return Attribute::get(fn () => $this->data['email'] ?? null);
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => $this->data['name'] ?? null);
    }

    protected function excerpt(): Attribute
    {
        return Attribute::get(function () {
            $data = $this->data ?? [];
            $filtered = collect($data)->except(['name', 'email']);
            if ($filtered->isEmpty()) {
                return '';
            }

            return $filtered->map(function ($value, $key) {
                $titleKey = ucwords(str_replace('_', ' ', $key));
                $stringValue = is_array($value) ? json_encode($value) : (string) $value;
                $excerpt = mb_strlen($stringValue) > 80 ? mb_substr($stringValue, 0, 77).'...' : $stringValue;

                return "$titleKey: $excerpt";
            })->implode(' · ');
        });
    }

    /**
     * Mark the submission as seen
     */
    public function markAsSeen(): void
    {
        if ($this->seen_at === null) {
            $this->update(['seen_at' => now()]);
        }
    }

    /**
     * Check if the submission is new (not seen)
     */
    public function isNew(): bool
    {
        return $this->seen_at === null;
    }

    /**
     * Check if the submission has been seen
     */
    public function isSeen(): bool
    {
        return $this->seen_at !== null;
    }

    /**
     * Scope a query to only include new submissions
     */
    public function scopeNew($query)
    {
        return $query->whereNull('seen_at');
    }

    /**
     * Scope a query to only include seen submissions
     */
    public function scopeSeen($query)
    {
        return $query->whereNotNull('seen_at');
    }
}
