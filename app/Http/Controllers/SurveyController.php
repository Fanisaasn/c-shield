<?php

namespace App\Http\Controllers;

use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    /**
     * Store a visitor's response to the general satisfaction survey.
     */
    public function store(Request $request): RedirectResponse
    {
        $questionIds = SurveyQuestion::query()->pluck('id');

        $rules = [
            'name' => ['nullable', 'string', 'max:255'],
            'service_date' => ['required', 'date'],
            'education' => ['required', 'string', 'max:255'],
            'age' => ['required', 'integer', 'min:5', 'max:120'],
            'occupation' => ['required', 'string', 'max:255'],
            'is_disability' => ['nullable', 'boolean'],
            'disability_types' => ['nullable', 'array'],
            'disability_types.*' => ['string', 'in:Fisik,Intelektual,Mental,Sensorik'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];

        foreach ($questionIds as $questionId) {
            $rules["answers.$questionId"] = ['required', 'integer', 'between:1,4'];
        }

        $data = $request->validate($rules);

        $isDisability = $request->boolean('is_disability');

        $response = SurveyResponse::create([
            'name' => $data['name'] ?? null,
            'service_date' => $data['service_date'],
            'education' => $data['education'],
            'age' => $data['age'],
            'occupation' => $data['occupation'],
            'is_disability' => $isDisability,
            'disability_types' => $isDisability ? ($data['disability_types'] ?? []) : null,
            'message' => $data['message'] ?? null,
        ]);

        foreach ($data['answers'] ?? [] as $questionId => $answer) {
            $response->answers()->create([
                'survey_question_id' => $questionId,
                'answer' => $answer,
            ]);
        }

        return back()->with('survey_success', 'Terima kasih, survei Anda telah kami terima.');
    }
}
