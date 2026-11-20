<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Cleans an uploaded SVG so it can be served safely, instead of rejecting
 * every file that merely contains a suspicious-looking string.
 *
 * Design tools add harmless things a plain blacklist trips over (Adobe
 * Illustrator always declares <!ENTITY ns_extend ...>). This keeps the
 * drawing and removes what can run code or reach outside the file:
 * scripts, event handlers, javascript:/external links, embedded HTML,
 * and external entities (XXE).
 */
class SvgSanitizer
{
    /** Elements removed together with their content. */
    private const BLOCKED_ELEMENTS = [
        'script', 'foreignobject', 'iframe', 'embed', 'object', 'audio', 'video',
        'handler', 'listener', 'set', 'meta', 'link', 'base',
    ];

    /**
     * Return the cleaned SVG markup, or null when the file is not a usable SVG.
     */
    public static function sanitize(string $content): ?string
    {
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content); // UTF-8 BOM
        $content = self::inlineEntities($content);

        if ($content === null) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        // LIBXML_NONET: never fetch anything over the network. No LIBXML_NOENT, so entities are not expanded.
        $loaded = $dom->loadXML($content, LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || ! $dom->documentElement || strtolower($dom->documentElement->localName) !== 'svg') {
            return null;
        }

        // Any DOCTYPE left over is dropped; the SVG renders fine without it.
        if ($dom->doctype) {
            $dom->removeChild($dom->doctype);
        }

        $xpath = new DOMXPath($dom);

        // Processing instructions (PHP tags, xml-stylesheet, ...).
        foreach (iterator_to_array($xpath->query('//processing-instruction()')) as $node) {
            $node->parentNode?->removeChild($node);
        }

        foreach (iterator_to_array($dom->getElementsByTagName('*')) as $element) {
            /** @var DOMElement $element */
            if (! $element->parentNode) {
                continue; // already removed with an ancestor
            }

            $name = strtolower($element->localName);

            if (in_array($name, self::BLOCKED_ELEMENTS, true)
                || (str_starts_with($name, 'animate') && self::animatesLink($element))) {
                $element->parentNode->removeChild($element);
                continue;
            }

            if ($name === 'style' && self::unsafeCss($element->textContent)) {
                $element->parentNode->removeChild($element);
                continue;
            }

            foreach (iterator_to_array($element->attributes) as $attribute) {
                $attrName = strtolower($attribute->localName);
                $value = trim($attribute->value);

                $unsafe = str_starts_with($attrName, 'on')
                    || (in_array($attrName, ['href', 'src', 'action', 'formaction'], true) && ! self::safeLink($value, $name))
                    || ($attrName === 'style' && self::unsafeCss($value))
                    || self::hasScriptUrl($value);

                if ($unsafe) {
                    $element->removeAttributeNode($attribute);
                }
            }
        }

        $clean = $dom->saveXML($dom->documentElement);

        return $clean === false ? null : '<?xml version="1.0" encoding="UTF-8"?>'."\n".$clean;
    }

    /**
     * Replace simple internal entities (e.g. Illustrator's &ns_extend;) with
     * their literal text and drop the DOCTYPE. External entities make the
     * file unusable (null): they are the XXE attack vector.
     */
    private static function inlineEntities(string $content): ?string
    {
        if (! preg_match('/<!DOCTYPE\b/i', $content)) {
            return $content;
        }

        if (preg_match('/<!ENTITY\s+%?\s*[\w.-]+\s+(SYSTEM|PUBLIC)\b/i', $content)) {
            return null;
        }

        preg_match_all('/<!ENTITY\s+([\w.-]+)\s+(["\'])(.*?)\2\s*>/is', $content, $matches, PREG_SET_ORDER);

        $content = preg_replace('/<!DOCTYPE\b[^\[>]*(\[.*?\]\s*)?>/is', '', $content, 1);

        foreach ($matches as [, $name, , $value]) {
            if (str_contains($value, '<') || str_contains($value, '&')) {
                return null; // entities that build markup are not allowed
            }
            $content = str_replace('&'.$name.';', htmlspecialchars($value, ENT_QUOTES | ENT_XML1), $content);
        }

        return $content;
    }

    private static function safeLink(string $value, string $element): bool
    {
        $value = preg_replace('/[\s\x00-\x1F]+/', '', $value);

        if ($value === '' || str_starts_with($value, '#')) {
            return true; // internal reference, e.g. <use href="#icon">
        }

        // Embedded bitmap images are fine; embedded SVG/HTML could carry scripts.
        if (preg_match('#^data:image/(png|jpe?g|gif|webp|bmp);#i', $value)) {
            return true;
        }

        // Plain web links are allowed on <a>; images must stay inside the file.
        return $element === 'a' && preg_match('#^https?://#i', $value) === 1;
    }

    private static function hasScriptUrl(string $value): bool
    {
        return preg_match('/(java|vb)script\s*:|data\s*:\s*text\/html/i', preg_replace('/[\s\x00-\x1F]+/', '', $value)) === 1;
    }

    private static function unsafeCss(string $css): bool
    {
        return preg_match('/expression\s*\(|javascript\s*:|vbscript\s*:|@import|behavior\s*:|-moz-binding|url\s*\(\s*["\']?\s*(?!#|data:image\/(png|jpe?g|gif|webp))/i', $css) === 1;
    }

    private static function animatesLink(DOMElement $element): bool
    {
        return str_contains(strtolower($element->getAttribute('attributeName')), 'href')
            || self::hasScriptUrl($element->getAttribute('to').$element->getAttribute('values').$element->getAttribute('from'));
    }
}
