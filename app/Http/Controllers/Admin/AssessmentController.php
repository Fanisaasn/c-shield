<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentCategory;
use App\Models\AssessmentQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssessmentController extends Controller
{
    public function index()
    {
        $categories = AssessmentCategory::with(['questions.options'])->orderBy('order')->get();
        return view('admin.assessments.index', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate(['name'=>['required','string','max:255'],'description'=>['nullable','string']]);
        $data['order'] = ((int) AssessmentCategory::max('order')) + 1;
        $data['slug'] = $this->uniqueSlug($data['name']);
        AssessmentCategory::create($data);
        return back()->with('success', 'Kategori assessment ditambahkan.');
    }

    public function updateCategory(Request $request, AssessmentCategory $category): RedirectResponse
    {
        $data = $request->validate(['name'=>['required','string','max:255'],'description'=>['nullable','string']]);
        $data['slug'] = $this->uniqueSlug($data['name'], $category->id);
        $category->update($data);
        return back()->with('success', 'Kategori assessment diperbarui.');
    }

    public function destroyCategory(AssessmentCategory $category): RedirectResponse { $category->delete(); return back()->with('success', 'Kategori assessment dihapus.'); }

    public function storeQuestion(Request $request): RedirectResponse
    {
        $data = $this->questionData($request);
        $data['order'] = ((int) AssessmentQuestion::where('assessment_category_id', $data['assessment_category_id'])->max('order')) + 1;
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

    private function questionData(Request $request): array { return $request->validate(['assessment_category_id'=>['required','exists:assessment_categories,id'],'question'=>['required','string'],'options'=>['required','array','min:2'],'options.*'=>['required','string','max:255'],'correct_option'=>['required','integer','min:0']]); }
    private function syncOptions(AssessmentQuestion $question, array $options, int $correct): void { foreach ($options as $order => $text) $question->options()->create(['option_text'=>$text,'order'=>$order,'is_correct'=>$order === $correct]); }
    private function uniqueSlug(string $name, ?int $ignore = null): string { $base = Str::slug($name) ?: 'kategori'; $slug = $base; $i = 2; while (AssessmentCategory::where('slug',$slug)->when($ignore, fn($q)=>$q->whereKeyNot($ignore))->exists()) $slug = $base.'-'.$i++; return $slug; }
}
