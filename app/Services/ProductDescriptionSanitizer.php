<?php

namespace App\Services;

/** Keeps basic product-description formatting while removing executable markup. */
final class ProductDescriptionSanitizer
{
    private const ALLOWED = ['p', 'br', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'strong', 'b', 'em', 'i', 'u', 'blockquote', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'a'];
    private const DROP_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'];

    public static function sanitize(string $html): string
    {
        if (strlen($html) > 200000) throw new \DomainException('Deskripsi maksimal 200 KB.');
        if (!class_exists(\DOMDocument::class)) throw new \RuntimeException('Ekstensi DOM diperlukan untuk memproses deskripsi.');

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        try {
            $doc->loadHTML('<?xml encoding="utf-8" ?><div id="product-description-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $root = $doc->getElementById('product-description-root');
        if (!$root) return '';
        $output = '';
        foreach (iterator_to_array($root->childNodes) as $node) $output .= self::cleanNode($node, $doc);
        return trim($output);
    }

    private static function cleanNode(\DOMNode $node, \DOMDocument $target): string
    {
        if ($node instanceof \DOMText) return htmlspecialchars($node->nodeValue ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        if (!$node instanceof \DOMElement) return '';
        $tag = strtolower($node->tagName);
        if (in_array($tag, self::DROP_CONTENT, true)) return '';
        $children = '';
        foreach (iterator_to_array($node->childNodes) as $child) $children .= self::cleanNode($child, $target);
        if (!in_array($tag, self::ALLOWED, true)) return $children;
        if ($tag === 'br') return '<br>';
        if ($tag !== 'a') return '<'.$tag.'>'.$children.'</'.$tag.'>';

        $href = trim($node->getAttribute('href'));
        $scheme = strtolower((string)parse_url($href, PHP_URL_SCHEME));
        $safe = ($scheme === 'http' || $scheme === 'https') || (str_starts_with($href, '/') && !str_starts_with($href, '//'));
        if (!$safe || $href === '') return $children;
        return '<a href="'.htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8').'" rel="noopener noreferrer">'.$children.'</a>';
    }
}
