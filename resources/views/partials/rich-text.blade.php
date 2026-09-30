@php
    $richRaw = (string) $html;

    if (! preg_match('/<\/?[a-z][^>]*>/i', $richRaw)) {
        // Plain-text entry (saved before the rich editor existed).
        $richSafe = nl2br(e($richRaw), false);
    } else {
        $richSafe = strip_tags($richRaw, '<div><p><br><strong><b><em><i><del><s><a><ul><ol><li><h1><h2><h3><blockquote><pre>');
        $richSafe = preg_replace('/\s(on\w+|style)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $richSafe);
        $richSafe = preg_replace('/href\s*=\s*(["\']?)\s*(javascript|data|vbscript):[^"\'>]*\1/i', 'href="#"', $richSafe);
        $richSafe = str_replace('<a ', '<a target="_blank" rel="noopener" ', $richSafe);
    }
@endphp
<div class="rich-text {{ $class ?? '' }}">{!! $richSafe !!}</div>
