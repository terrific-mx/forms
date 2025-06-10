<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Form extends Model
{
    use HasFactory;

    protected $casts = [
        'turnstile_secret_key' => 'encrypted',
    ];

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

    public function blockedEmails()
    {
        return $this->hasMany(BlockedEmail::class);
    }

    protected function forwardToEmails(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => empty($attributes['forward_to'] ?? null)
                ? []
                : array_filter(array_map('trim', preg_split('/\r?\n/', $attributes['forward_to']))));
    }

    protected function allowedDomainsList(): Attribute
    {
        return Attribute::get(fn ($value, $attributes) => empty($attributes['allowed_domains'] ?? null)
                ? []
                : array_filter(array_map('trim', explode(',', $attributes['allowed_domains']))));
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
                return asset('storage/'.$this->logo_path);
            }

            return null;
        });
    }

    public function isReferrerAllowed(?string $referrer): bool
    {
        // If no allowed domains are configured, allow all referrers
        if (empty($this->allowed_domains_list)) {
            return true;
        }

        // If no referrer is provided, allow the submission (direct access)
        if (empty($referrer)) {
            return true;
        }

        $referrerHost = parse_url($referrer, PHP_URL_HOST);

        if (! $referrerHost) {
            return false;
        }

        foreach ($this->allowed_domains_list as $allowedDomain) {
            if ($referrerHost === $allowedDomain || str_ends_with($referrerHost, '.'.$allowedDomain)) {
                return true;
            }
        }

        return false;
    }

    public function isHoneypotTriggered(array $data): bool
    {
        // If no honeypot field is configured, allow all submissions
        if (empty($this->honeypot_field)) {
            return false;
        }

        // Check if the honeypot field has any value (after trimming whitespace)
        $honeypotValue = trim($data[$this->honeypot_field] ?? '');

        return ! empty($honeypotValue);
    }

    public function isTurnstileValid(?string $turnstileResponse, ?string $remoteIp = null): bool
    {
        // If no Turnstile secret key is configured, allow all submissions
        if (empty($this->turnstile_secret_key)) {
            return true;
        }

        // If Turnstile is configured but no response token provided, reject
        if (empty($turnstileResponse)) {
            return false;
        }

        try {
            $response = Http::asForm()->post(config('services.turnstile.verify_url'), [
                'secret' => $this->turnstile_secret_key,
                'response' => $turnstileResponse,
                'remoteip' => $remoteIp,
            ]);

            if (! $response->successful()) {
                return false;
            }

            $result = $response->json();

            return $result['success'] ?? false;
        } catch (\Exception $e) {
            // Log the error if needed, but fail closed for security
            return false;
        }
    }

    public function isEmailBlocked(array $data): bool
    {
        // If no blocked emails are configured, allow all submissions
        if ($this->blockedEmails()->count() === 0) {
            return false;
        }

        // Common email field names to check
        $emailFields = ['email', 'user_email', 'contact_email', 'from', 'sender', 'reply_to'];

        foreach ($emailFields as $field) {
            $emailValue = $data[$field] ?? null;

            if (empty($emailValue)) {
                continue;
            }

            // Normalize email for comparison (lowercase and trim)
            $normalizedEmail = strtolower(trim($emailValue));

            // Check if this email is in the blocked list
            if ($this->blockedEmails()->where('email', $normalizedEmail)->exists()) {
                return true;
            }
        }

        return false;
    }
}
