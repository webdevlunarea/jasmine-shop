<?php

namespace App\Controllers\Api\V1;

use App\Models\ArtikelModel;
use App\Services\Api\ApiPagination;

/** Public read-only article feed used by future native clients. */
final class ArticleController extends ApiController
{
    public function index()
    {
        $pagination = ApiPagination::resolve($this->request->getGet('page'), $this->request->getGet('per_page'), 10, 30);
        if ($pagination === null) return $this->failure('VALIDATION_ERROR', 'Nomor halaman atau jumlah artikel tidak valid.', 422);

        try {
            $model = new ArtikelModel();
            $category = trim((string)$this->request->getGet('category'));
            $query = trim((string)$this->request->getGet('q'));
            if (mb_strlen($category) > 80 || mb_strlen($query) > 120) {
                return $this->failure('VALIDATION_ERROR', 'Filter artikel terlalu panjang.', 422);
            }
            $countModel = new ArtikelModel();
            if ($category !== '') { $model->like('kategori', $category); $countModel->like('kategori', $category); }
            if ($query !== '') {
                $model->groupStart()->like('judul', $query)->orLike('isi', $query)->groupEnd();
                $countModel->groupStart()->like('judul', $query)->orLike('isi', $query)->groupEnd();
            }
            $total = $countModel->countAllResults();
            $rows = $model->select('id,judul,path,penulis,waktu,kategori,suka,bagikan,LEFT(isi, 1500) AS excerpt_source, (header IS NOT NULL AND OCTET_LENGTH(header) > 0) AS has_header', false)
                ->orderBy('id', 'DESC')->findAll($pagination['per_page'], $pagination['offset']);

            return $this->success(['articles' => array_map([$this, 'articleSummary'], $rows)], [
                'page' => $pagination['page'], 'per_page' => $pagination['per_page'],
                'total' => $total, 'pages' => (int)ceil($total / $pagination['per_page']),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API article feed failed: ' . get_class($e));
            return $this->failure('ARTICLES_UNAVAILABLE', 'Artikel belum dapat dimuat.', 503);
        }
    }

    public function show(string $slug)
    {
        try {
            $row = (new ArtikelModel())->where('path', $slug)->first();
            if (!$row) return $this->failure('ARTICLE_NOT_FOUND', 'Artikel tidak ditemukan.', 404);

            $article = $this->articleSummary($row);
            $article['content'] = html_entity_decode(strip_tags((string)($row['isi'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $article['keywords'] = trim((string)($row['keywords'] ?? ''));
            return $this->success(['article' => $article]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API article detail failed: ' . get_class($e));
            return $this->failure('ARTICLE_UNAVAILABLE', 'Artikel belum dapat dibuka.', 503);
        }
    }

    private function articleSummary(array $row): array
    {
        $id = (string)($row['id'] ?? '');
        $hasImage = !empty($row['has_header']) || (is_string($row['header'] ?? null) && $row['header'] !== '');
        $content = html_entity_decode(strip_tags((string)($row['excerpt_source'] ?? $row['isi'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return [
            'id' => $id,
            'title' => trim((string)($row['judul'] ?? '')),
            'slug' => trim((string)($row['path'] ?? '')),
            'author' => trim((string)($row['penulis'] ?? '')),
            'published_at' => trim((string)($row['waktu'] ?? '')),
            'category' => trim((string)($row['kategori'] ?? '')),
            'excerpt' => mb_substr(preg_replace('/\s+/u', ' ', $content), 0, 240),
            'image_url' => $hasImage ? site_url('imgart/' . rawurlencode($id)) : null,
            'likes' => max(0, (int)($row['suka'] ?? 0)),
            'shares' => max(0, (int)($row['bagikan'] ?? 0)),
        ];
    }
}
