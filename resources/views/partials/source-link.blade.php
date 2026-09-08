@php
    $label = 'Lihat Sumber Asli';

    if (str_contains($sourceUrl, 'instagram.com')) {
        $label = 'Lihat di Instagram';
    } elseif (str_contains($sourceUrl, 'youtube.com') || str_contains($sourceUrl, 'youtu.be')) {
        $label = 'Tonton di YouTube';
    } elseif (str_contains($sourceUrl, 'tiktok.com')) {
        $label = 'Lihat di TikTok';
    } elseif (str_contains($sourceUrl, 'facebook.com')) {
        $label = 'Lihat di Facebook';
    } elseif (str_contains($sourceUrl, 'twitter.com') || str_contains($sourceUrl, 'x.com')) {
        $label = 'Lihat di X';
    }
@endphp

<a href="{{ $sourceUrl }}" target="_blank" rel="noopener noreferrer"
   class="inline-flex items-center gap-2 rounded-md bg-navy-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-navy-800">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
        <path d="M14 5h5v5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M19 5 10 14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M18 13v5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    {{ $label }}
</a>
