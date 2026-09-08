<?php

namespace App\Http\Controllers\Admin;

use App\Models\Flyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FlyerController extends ContentController
{
    protected string $model = Flyer::class;
    protected string $view = 'flyers';
    protected string $route = 'flyers';
    protected array $rules = [
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'source_url' => ['nullable', 'url', 'max:255'],
        'images' => ['nullable', 'array'],
        'images.*' => ['image', 'max:4096'],
        'is_published' => ['nullable', 'boolean'],
        'published_at' => ['nullable', 'date'],
    ];

    public function store(Request $request)
    {
        $flyer = Flyer::create($this->flyerData($request));
        $this->storeImages($flyer, $request);

        return redirect()->route('admin.flyers.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function update(Request $request, $item)
    {
        $flyer = $this->findItem($item);
        $flyer->update($this->flyerData($request));

        $this->deleteImages($flyer, $request->input('delete_images', []));
        $this->storeImages($flyer, $request);

        return redirect()->route('admin.flyers.index')->with('success', 'Data berhasil diperbarui.');
    }

    protected function flyerData(Request $request): array
    {
        $data = $request->validate($this->rules);
        unset($data['images']);

        $data['is_published'] = $request->boolean('is_published');
        if ($data['is_published'] && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    protected function storeImages(Flyer $flyer, Request $request): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $sortOrder = (int) $flyer->images()->max('sort_order') + 1;
        foreach ($request->file('images') as $file) {
            $flyer->images()->create([
                'image' => $file->store('flyers', 'public'),
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    protected function deleteImages(Flyer $flyer, array $imageIds): void
    {
        if (empty($imageIds)) {
            return;
        }

        $images = $flyer->images()->whereIn('id', $imageIds)->get();
        foreach ($images as $image) {
            Storage::disk('public')->delete($image->image);
            $image->delete();
        }
    }
}
