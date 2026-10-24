<?php

namespace App\Services;

use CodeIgniter\HTTP\ResponseInterface;

/** Only for public catalog pictures, never customer pictures or payment proofs. */
final class PublicCatalogImage
{
    public static function productUrl(array $product): string
    {
        $id = (string) ($product['id'] ?? '');
        if ($id === '') {
            return '/img/logo%20icon.png';
        }

        // Listing queries intentionally omit the image BLOB. The image endpoint
        // remains the single source of truth and returns its own ETag.
        return '/imgpic/' . rawurlencode($id);
    }

    public static function respond(ResponseInterface $response, ?string $bytes, string $ifNoneMatch = '', string $version = ''): ResponseInterface
    {
        // CI4's Response constructor adds a no-cache header. Remove both queued
        // and already queued PHP headers before applying the public-image policy.
        if (function_exists('header_remove') && !headers_sent()) {
            header_remove('Cache-Control');
        }
        $response->removeHeader('Cache-Control');
        $info = $bytes ? @getimagesizefromstring($bytes) : false;
        $mime = $info['mime'] ?? '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'], true)) {
            return $response->setStatusCode(404)->setHeader('Cache-Control', 'no-store')->setBody('');
        }

        $etag = '"' . hash('sha256', $bytes) . '"';
        $hasCurrentVersion = preg_match('/^[a-f0-9]{32}$/i', $version) === 1
            && hash_equals(substr(hash('sha256', $bytes), 0, 32), strtolower($version));
        // Versioned gallery URLs are content-addressed; unversioned URLs retain
        // a short TTL so edits are visible without cache-busting URLs.
        $response->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', $hasCurrentVersion ? 'public, max-age=31536000, immutable' : 'public, max-age=300, must-revalidate')
            ->setHeader('ETag', $etag)
            ->setHeader('X-Content-Type-Options', 'nosniff');

        foreach (explode(',', $ifNoneMatch) as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '*' || preg_replace('/^W\//', '', $candidate) === $etag) {
                return $response->setStatusCode(304)->setBody('');
            }
        }

        return $response->setStatusCode(200)->setBody($bytes);
    }
}
