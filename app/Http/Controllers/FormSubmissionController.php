<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Notifications\FormSubmissionReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class FormSubmissionController extends Controller
{
    public function store(Request $request, $ulid)
    {
        $form = Form::where('ulid', $ulid)->firstOrFail();

        $referrer = $request->headers->get('referer');

        // Check allowed domains if configured
        if (! $form->isReferrerAllowed($referrer)) {
            abort(403, 'Submission not allowed from this domain');
        }

        $data = $request->all();

        // Check honeypot field if configured
        if ($form->isHoneypotTriggered($data)) {
            // Return 200 status to avoid giving bots feedback, but don't process the submission
            return $this->redirectToThankYou($form);
        }

        // Check Turnstile if configured
        if (! $form->isTurnstileValid($request->input('cf-turnstile-response'), $request->ip())) {
            abort(403, 'Turnstile validation failed');
        }

        // Check if email is blocked
        if ($form->isEmailBlocked($data)) {
            // Return 200 status to avoid giving feedback, but don't process the submission
            return $this->redirectToThankYou($form);
        }

        $submission = $form->submissions()->create([
            'data' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $referrer,
        ]);

        collect($form->forward_to_emails)
            ->each(fn ($email) => Notification::route('mail', $email)
                ->notify(new FormSubmissionReceived($form, $submission)));

        return $this->redirectToThankYou($form);
    }

    private function redirectToThankYou(Form $form)
    {
        return redirect($form->redirect_url ?: "/f/{$form->ulid}/thank-you");
    }
}
