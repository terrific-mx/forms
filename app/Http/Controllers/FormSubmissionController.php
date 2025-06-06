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

        $data = $request->all();

        $submission = $form->submissions()->create([
            'data' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
        ]);

        // Notify all emails in forward_to if set
        if (!empty($form->forward_to)) {
            $emails = array_filter(array_map('trim', preg_split('/[;,\n]+/', $form->forward_to)));
            foreach ($emails as $email) {
                Notification::route('mail', $email)
                    ->notify(new FormSubmissionReceived($form, $submission));
            }
        }

        return redirect("/f/{$form->ulid}/thank-you");
    }
}
