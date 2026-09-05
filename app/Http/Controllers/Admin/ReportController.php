<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentCategory;
use Illuminate\Http\Request;
class ReportController extends Controller {
 public function index(Request $request) {
  $attempts = AssessmentAttempt::with('user','answers.question.category')->whereNotNull('completed_at')->when($request->filled('type'),fn($q)=>$q->where('type',$request->type))->when($request->filled('level'),fn($q)=>$q->where('level',$request->level))->when($request->filled('from'),fn($q)=>$q->whereDate('completed_at','>=',$request->from))->when($request->filled('to'),fn($q)=>$q->whereDate('completed_at','<=',$request->to))->latest('completed_at')->paginate(20)->withQueryString();
  return view('admin.reports.index',['attempts'=>$attempts,'categories'=>AssessmentCategory::orderBy('order')->get(),'levels'=>['Sangat Rendah','Rendah','Cukup','Baik','Sangat Baik']]);
 }
}
