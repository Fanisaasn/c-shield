@php
    $youtubeId = null;
    $isInstagram = false;

    if ($video->video_url) {
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/i', $video->video_url, $matches)) {
            $youtubeId = $matches[1];
        } elseif (str_contains($video->video_url, 'instagram.com')) {
            $isInstagram = true;
        }
    }

    $iconClass ??= 'h-14 w-14';
@endphp

@if ($youtubeId)
    <div class="aspect-video overflow-hidden rounded-xl bg-navy-900">
        <iframe class="h-full w-full" src="https://www.youtube.com/embed/{{ $youtubeId }}" title="{{ $video->title }}" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
    </div>
@elseif ($isInstagram)
    <div class="overflow-hidden rounded-xl">
        <blockquote class="instagram-media" data-instgrm-permalink="{{ $video->video_url }}" data-instgrm-version="14" style="margin:0 auto;width:100%;background:#fff;"></blockquote>
    </div>
    @push('scripts')
        <script async src="https://www.instagram.com/embed.js"></script>
    @endpush
@elseif ($video->video_path)
    <div class="flex aspect-video items-center justify-center overflow-hidden rounded-xl bg-navy-900">
        <video controls preload="metadata" class="h-full w-full" @if($video->thumbnail) poster="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($video->thumbnail) }}" @endif>
            <source src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($video->video_path) }}">
            Browser Anda tidak mendukung pemutaran video HTML5.
        </video>
    </div>
@elseif ($video->video_url)
    <a href="{{ $video->video_url }}" target="_blank" rel="noopener noreferrer" class="group relative flex aspect-video items-center justify-center overflow-hidden rounded-xl bg-navy-900">
        @if ($video->thumbnail)
            <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" class="absolute inset-0 h-full w-full object-cover">
        @endif
        <span class="relative flex h-14 w-14 items-center justify-center rounded-full bg-black/50 text-white transition group-hover:bg-black/70">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M8 5v14l11-7-11-7Z" fill="currentColor"/></svg>
        </span>
    </a>
@elseif ($video->thumbnail)
    <div class="flex aspect-video items-center justify-center overflow-hidden rounded-xl bg-navy-900">
        <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" class="h-full w-full object-cover">
    </div>
@else
    <div class="flex aspect-video items-center justify-center overflow-hidden rounded-xl bg-navy-900">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="{{ $iconClass }} text-teal-400">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.6"/>
            <path d="M10 9v6l5-3-5-3Z" fill="currentColor"/>
        </svg>
    </div>
@endif
