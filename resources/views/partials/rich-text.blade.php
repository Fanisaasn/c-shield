@php
    $richRaw = (string) $html;

    if (! preg_match('/<\/?[a-z][^>]*>/i', $richRaw)) {
        // Plain-text entry (saved before the rich editor existed).
        $richSafe = nl2br(e($richRaw), false);
    } else {
        $richSafe = strip_tags($richRaw, '<div><p><br><strong><b><em><i><del><s><a><ul><ol><li><h1><h2><h3><blockquote><pre><figure><figcaption><img><span>');
        $richSafe = preg_replace('/\s(on\w+|style|data-[\w-]+)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $richSafe);
        $richSafe = preg_replace('/(href|src)\s*=\s*(["\']?)\s*(javascript|data|vbscript):[^"\'>]*\2/i', '$1="#"', $richSafe);
        $richSafe = str_replace('<a ', '<a target="_blank" rel="noopener" ', $richSafe);
    }
@endphp
<div class="rich-text {{ $class ?? '' }}">{!! $richSafe !!}</div>
