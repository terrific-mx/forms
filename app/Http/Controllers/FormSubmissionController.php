<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function store(Request $request, $ulid)
    {
        $form = Form::where('ulid', $ulid)->firstOrFail();

        $validated = $request->validate([
            'data' => 'required|array',
        ]);

        $submission = $form->submissions()->create([
            'data' => $validated['data'],
        ]);

        return redirect("/f/{$form->ulid}/thank-you");
    }
}
