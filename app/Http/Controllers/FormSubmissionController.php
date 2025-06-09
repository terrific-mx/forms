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
        if (!$form->isReferrerAllowed($referrer)) {
            abort(403, 'Submission not allowed from this domain');
        }

        $data = $request->all();

        $submission = $form->submissions()->create([
            'data' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $referrer,
        ]);

        collect($form->forward_to_emails)
            ->each(fn ($email) => Notification::route('mail', $email)
                ->notify(new FormSubmissionReceived($form, $submission)));

        return redirect($form->redirect_url ?: "/f/{$form->ulid}/thank-you");
    }
}
