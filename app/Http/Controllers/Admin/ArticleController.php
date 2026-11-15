<?php

namespace App\Http\Controllers\Admin;

use App\Models\Article;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArticleController extends ContentController
{
    protected string $model = Article::class;
    protected string $view = 'articles';
    protected string $route = 'articles';
    protected ?string $uploadField = 'cover_image';
    protected ?string $slugField = 'slug';

    protected array $rules = [
        'title' => ['required', 'string', 'max:255'],
        'excerpt' => ['nullable', 'string', 'max:500'],
        'content' => ['required', 'string'],
        'source_url' => ['nullable', 'url', 'max:255'],
        'cover_image' => ['nullable', 'image', 'max:2048'],
        'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip', 'max:20480'],
        'remove_attachment' => ['nullable', 'boolean'],
        'is_published' => ['nullable', 'boolean'],
        'published_at' => ['nullable', 'date'],
    ];

    protected array $messages = [
        'attachment.mimes' => 'Lampiran harus berupa PDF, Word, PowerPoint, Excel, TXT, atau ZIP.',
        'attachment.max' => 'Ukuran lampiran maksimal 20 MB.',
    ];

    protected function validated(Request $request, ?Model $item = null): array
    {
        $data = parent::validated($request, $item);
        unset($data['attachment'], $data['remove_attachment']);

        if ($request->hasFile('attachment')) {
            $this->deleteAttachment($item);
            $file = $request->file('attachment');
            $data['attachment'] = $file->store('articles/attachments', 'public');
            $data['attachment_name'] = $file->getClientOriginalName();
        } elseif ($item && $request->boolean('remove_attachment')) {
            $this->deleteAttachment($item);
            $data['attachment'] = null;
            $data['attachment_name'] = null;
        }

        return $data;
    }

    public function destroy($item)
    {
        $this->deleteAttachment($this->findItem($item));
        return parent::destroy($item);
    }

    private function deleteAttachment(?Model $item): void
    {
        if ($item?->attachment) {
            Storage::disk('public')->delete($item->attachment);
        }
    }
}
