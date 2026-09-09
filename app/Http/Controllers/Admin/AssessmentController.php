<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    public function index()
    {
        $questions = AssessmentQuestion::with('options')->orderBy('order')->get();
        return view('admin.assessments.index', compact('questions'));
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        $data = $this->questionData($request);
        $data['order'] = ((int) AssessmentQuestion::max('order')) + 1;
        $question = AssessmentQuestion::create($data);
        $this->syncOptions($question, $request->input('options', []), $request->input('correct_option'));
        return back()->with('success', 'Pertanyaan ditambahkan.');
    }

    public function updateQuestion(Request $request, AssessmentQuestion $question): RedirectResponse
    {
        $question->update($this->questionData($request));
        $question->options()->delete();
        $this->syncOptions($question, $request->input('options', []), $request->input('correct_option'));
        return back()->with('success', 'Pertanyaan diperbarui.');
    }

    public function destroyQuestion(AssessmentQuestion $question): RedirectResponse { $question->delete(); return back()->with('success', 'Pertanyaan dihapus.'); }

    private function questionData(Request $request): array { return $request->validate(['question'=>['required','string'],'options'=>['required','array','min:2'],'options.*'=>['required','string','max:255'],'correct_option'=>['required','integer','min:0']]); }
    private function syncOptions(AssessmentQuestion $question, array $options, int $correct): void { foreach ($options as $order => $text) $question->options()->create(['option_text'=>$text,'order'=>$order,'is_correct'=>$order === $correct]); }
}
