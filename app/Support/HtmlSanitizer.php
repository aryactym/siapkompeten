<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Sanitasi HTML berbasis allowlist untuk konten CMS.
 *
 * Strategi: elemen berbahaya dibuang beserta isinya, elemen tak dikenal
 * di-unwrap (anak-anaknya dipertahankan), atribut di luar allowlist dihapus,
 * dan skema URL divalidasi agar `javascript:`, `data:`, dan `vbscript:`
 * tidak lolos.
 */
class HtmlSanitizer
{
    /**
     * Elemen yang dibuang total beserta isinya.
     *
     * @var list<string>
     */
    private const DANGEROUS_TAGS = [
        'script', 'style', 'iframe', 'object', 'embed', 'applet', 'base',
        'form', 'input', 'button', 'select', 'option', 'textarea', 'link',
        'meta', 'svg', 'math', 'template', 'noscript',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'small', 'sub', 'sup',
        'ul', 'ol', 'li', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'code',
        'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption',
        'div', 'span',
    ];

    /**
     * @var array<string, list<string>>
     */
    private const ALLOWED_ATTRIBUTES = [
        '*' => ['class'],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'td' => ['colspan', 'rowspan'],
        'th' => ['colspan', 'rowspan', 'scope'],
    ];

    /**
     * @var list<string>
     */
    private const SAFE_URL_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        if (trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);

        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="__sanitizer_root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('__sanitizer_root');

        if (! $root instanceof DOMElement) {
            return '';
        }

        self::cleanChildren($root);

        $output = '';

        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    private static function cleanChildren(DOMNode $parent): void
    {
        $child = $parent->firstChild;

        while ($child !== null) {
            $next = $child->nextSibling;

            if ($child instanceof DOMElement) {
                self::cleanElement($child);
            }

            $child = $next;
        }
    }

    private static function cleanElement(DOMElement $element): void
    {
        $tag = strtolower($element->nodeName);

        if (in_array($tag, self::DANGEROUS_TAGS, true)) {
            $element->parentNode?->removeChild($element);

            return;
        }

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            self::cleanChildren($element);
            self::unwrap($element);

            return;
        }

        self::cleanAttributes($element);
        self::cleanChildren($element);
    }

    private static function cleanAttributes(DOMElement $element): void
    {
        $tag = strtolower($element->nodeName);
        $allowed = array_merge(
            self::ALLOWED_ATTRIBUTES['*'] ?? [],
            self::ALLOWED_ATTRIBUTES[$tag] ?? []
        );

        $toRemove = [];

        foreach ($element->attributes as $attribute) {
            $name = strtolower($attribute->nodeName);

            if (! in_array($name, $allowed, true)) {
                $toRemove[] = $name;

                continue;
            }

            if (in_array($name, ['href', 'src'], true) && ! self::isSafeUrl($attribute->nodeValue)) {
                $toRemove[] = $name;
            }
        }

        foreach ($toRemove as $name) {
            $element->removeAttribute($name);
        }

        if ($tag === 'a' && strtolower($element->getAttribute('target')) === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function isSafeUrl(?string $url): bool
    {
        $url = trim((string) $url);

        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return true;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        if ($scheme === null || $scheme === false) {
            return true;
        }

        return in_array(strtolower($scheme), self::SAFE_URL_SCHEMES, true);
    }

    private static function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;

        if ($parent === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
