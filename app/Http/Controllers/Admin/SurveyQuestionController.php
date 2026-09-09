<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SurveyQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SurveyQuestionController extends Controller
{
    public function index()
    {
        $questions = SurveyQuestion::query()->orderBy('sort_order')->get();

        return view('admin.survey-questions.index', compact('questions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
        ]);

        $data['sort_order'] = ((int) SurveyQuestion::max('sort_order')) + 1;
        SurveyQuestion::create($data);

        return back()->with('success', 'Pertanyaan survei ditambahkan.');
    }

    public function update(Request $request, SurveyQuestion $question): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $question->update($data);

        return back()->with('success', 'Pertanyaan survei diperbarui.');
    }

    public function destroy(SurveyQuestion $question): RedirectResponse
    {
        $question->delete();

        return back()->with('success', 'Pertanyaan survei dihapus.');
    }
}
