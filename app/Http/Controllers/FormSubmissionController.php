<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    public function store(Request $request, $ulid)
    {
        $form = Form::where('ulid', $ulid)->firstOrFail();

        $data = $request->all();

        $submission = $form->submissions()->create([
            'data' => $data,
        ]);

        return redirect("/f/{$form->ulid}/thank-you");
    }
}
