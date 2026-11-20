<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EditorUploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip'],
        ], [
            'file.mimes' => 'Format file tidak didukung. Gunakan gambar, PDF, Word, PowerPoint, Excel, TXT, atau ZIP.',
            'file.max' => 'Ukuran file maksimal 20 MB.',
        ]);

        $path = $request->file('file')->store('editor-uploads', 'public');

        return response()->json(['url' => asset('storage/' . $path)]);
    }
}
