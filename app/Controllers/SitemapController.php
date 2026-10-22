<?php

namespace App\Controllers;

use App\Models\ArtikelModel;
use App\Models\BarangModel;
use CodeIgniter\HTTP\ResponseInterface;

/** Serves a cached sitemap containing only public, indexable storefront URLs. */
class SitemapController extends BaseController
{
    private const CACHE_KEY = 'public_sitemap_xml_v1';
    private const CACHE_TTL = 1800;

    public function index(): ResponseInterface
    {
        $xml = cache()->get(self::CACHE_KEY);
        if (!is_string($xml) || $xml === '') {
            $xml = $this->buildSitemap();
            cache()->save(self::CACHE_KEY, $xml, self::CACHE_TTL);
        }

        return $this->response
            ->setContentType('application/xml', 'UTF-8')
            ->setHeader('Cache-Control', 'public, max-age=1800, s-maxage=1800')
            ->setBody($xml);
    }

    private function buildSitemap(): string
    {
        $urls = [
            'https://lunareafurniture.com/',
            'https://lunareafurniture.com/all',
            'https://lunareafurniture.com/article',
            'https://lunareafurniture.com/about',
            'https://lunareafurniture.com/contact',
            'https://lunareafurniture.com/faq',
            'https://lunareafurniture.com/kebijakan-privasi',
            'https://lunareafurniture.com/syarat-dan-ketentuan',
        ];

        $products = (new BarangModel())
            ->select('path')
            ->where('active', '1')
            ->where('path !=', '')
            ->findAll();
        foreach ($products as $product) {
            $path = trim((string) ($product['path'] ?? ''));
            if ($this->isSafeSlug($path)) {
                $urls[] = 'https://lunareafurniture.com/product/' . rawurlencode($path);
            }
        }

        $categories = (new BarangModel())
            ->select('subkategori')
            ->distinct()
            ->where('active', '1')
            ->where('subkategori !=', '')
            ->findAll();
        foreach ($categories as $category) {
            $path = trim((string) ($category['subkategori'] ?? ''));
            if ($this->isSafeSlug($path)) {
                $urls[] = 'https://lunareafurniture.com/all/' . rawurlencode($path);
            }
        }

        $articles = (new ArtikelModel())->select('path')->findAll();
        foreach ($articles as $article) {
            $path = trim((string) ($article['path'] ?? ''));
            if ($this->isSafeSlug($path)) {
                $urls[] = 'https://lunareafurniture.com/article/' . rawurlencode($path);
            }
        }

        $urls = array_values(array_unique($urls));
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= '  <url><loc>' . htmlspecialchars($url, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc></url>\n";
        }
        $xml .= '</urlset>' . "\n";

        return $xml;
    }

    private function isSafeSlug(string $slug): bool
    {
        return $slug !== ''
            && strlen($slug) <= 240
            && preg_match('/^[a-zA-Z0-9_-]+$/', $slug) === 1;
    }
}
