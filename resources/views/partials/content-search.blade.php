<form action="{{ route($searchRoute) }}" method="GET" role="search" data-content-search class="mb-6">
    <label for="content-search" class="mb-2 block text-sm font-semibold text-navy-900">Cari {{ $searchLabel }}</label>
    <div class="flex flex-wrap items-center gap-2">
        <input id="content-search" type="search" name="q" value="{{ $search }}" maxlength="200"
               placeholder="Cari berdasarkan judul..."
               class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/20">
        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Cari</button>
    </div>
    @error('q')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
</form>
