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

        return '/imgpic/' . rawurlencode($id) . '?v='
            . substr(hash('sha256', (string) ($product['gambar'] ?? '')), 0, 16);
    }

    public static function respond(ResponseInterface $response, ?string $bytes, string $ifNoneMatch = ''): ResponseInterface
    {
        // CI4 merges repeated headers: explicitly replace its default no-cache policy.
        $response->removeHeader('Cache-Control');
        $info = $bytes ? @getimagesizefromstring($bytes) : false;
        $mime = $info['mime'] ?? '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'], true)) {
            return $response->setStatusCode(404)->setHeader('Cache-Control', 'no-store')->setBody('');
        }

        $etag = '"' . hash('sha256', $bytes) . '"';
        // Short bounded freshness also protects callers using unversioned legacy URLs.
        $response->setHeader('Content-Type', $mime)
            ->setHeader('Cache-Control', 'public, max-age=300, must-revalidate')
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
