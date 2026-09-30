@php
    $isInteractive = $video->type === 'interactive' || $video->slug === 'jaga-data-jaga-diri';
    $interactiveUrl = $video->interactive_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($video->interactive_path)
        : asset('interactive-video/jaga-data-jaga-diri/index.html');
    $interactiveHue = abs(crc32($video->slug ?: $video->title)) % 360;
    $showInteractivePlayer ??= false;
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

@if ($isInteractive && $showInteractivePlayer)
    <div class="aspect-video overflow-hidden rounded-xl bg-navy-950 shadow-sm ring-1 ring-slate-900/10">
        <iframe class="h-full w-full border-0" src="{{ $interactiveUrl }}" title="Video interaktif: {{ $video->title }}" allow="autoplay; fullscreen" sandbox="allow-scripts allow-forms allow-modals allow-downloads" loading="eager" allowfullscreen></iframe>
    </div>
@elseif ($isInteractive)
    <div class="relative flex aspect-video items-center justify-center overflow-hidden rounded-xl bg-navy-950 text-white"
         @unless($video->thumbnail) style="background:linear-gradient(135deg,hsl({{ $interactiveHue }} 42% 20%),#050d1a 72%)" @endunless>
        @if($video->thumbnail)
            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($video->thumbnail) }}" alt="Thumbnail {{ $video->title }}" class="absolute inset-0 h-full w-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-navy-950/85 via-navy-950/10 to-transparent"></div>
        @else
            <div class="absolute -right-12 -top-14 h-44 w-44 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-16 -left-12 h-40 w-40 rounded-full bg-teal-400/10 blur-2xl"></div>
        @endif
        <div class="relative px-5 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-black/35 ring-1 ring-white/25 backdrop-blur-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M8 5v14l11-7-11-7Z" fill="currentColor"/></svg>
            </span>
            <span class="mt-3 block line-clamp-2 font-heading text-sm font-bold leading-snug text-white">{{ $video->title }}</span>
            <span class="mt-1 block text-[10px] font-semibold uppercase tracking-[0.18em] text-teal-300">Video interaktif</span>
        </div>
    </div>
@elseif ($youtubeId)
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
