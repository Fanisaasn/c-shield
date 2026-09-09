<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class ContentController extends Controller
{
    protected string $model;
    protected string $view;
    protected string $route;
    protected array $rules;
    protected ?string $uploadField = null;
    protected ?string $slugField = null;

    public function index()
    {
        $items = ($this->model)::latest()->paginate(12);
        return view("admin.{$this->view}.index", compact('items'));
    }

    public function create() { return view("admin.{$this->view}.form", ['item' => new $this->model]); }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        ($this->model)::create($data);
        return redirect()->route("admin.{$this->route}.index")->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit($item) { $item = $this->findItem($item); return view("admin.{$this->view}.form", compact('item')); }

    public function update(Request $request, $item)
    {
        $item = $this->findItem($item);
        $data = $this->validated($request, $item);
        $item->update($data);
        return redirect()->route("admin.{$this->route}.index")->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($item)
    {
        $item = $this->findItem($item);
        $this->deleteUpload($item);
        $item->delete();
        return redirect()->route("admin.{$this->route}.index")->with('success', 'Data berhasil dihapus.');
    }

    protected function validated(Request $request, ?Model $item = null): array
    {
        $rules = $this->rules;
        if ($this->slugField) $rules['slug'] = ['nullable', 'string', 'max:255', 'unique:'.$this->route.',slug'.($item ? ','.$item->id : '')];
        $data = $request->validate($rules);
        $data['is_published'] = $request->boolean('is_published');
        if ($this->slugField) $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        if (array_key_exists('published_at', $data) && !$data['published_at'] && $data['is_published']) $data['published_at'] = now();
        if ($this->uploadField && $request->hasFile($this->uploadField)) {
            $this->deleteUpload($item);
            $data[$this->uploadField] = $request->file($this->uploadField)->store($this->route, 'public');
        }
        return $data;
    }

    protected function deleteUpload(?Model $item): void
    {
        if (!$item || !$this->uploadField) return;
        $path = $item->{$this->uploadField};
        if ($path && !Str::startsWith($path, ['http://', 'https://'])) Storage::disk('public')->delete($path);
    }

    protected function findItem($item): Model
    {
        return $item instanceof Model ? $item : ($this->model)::findOrFail($item);
    }
}
