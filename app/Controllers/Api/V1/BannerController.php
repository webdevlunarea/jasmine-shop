<?php

namespace App\Controllers\Api\V1;

use App\Models\BannerModel;

/** Public, read-only feed of banners enabled by the website administrator. */
final class BannerController extends ApiController
{
    public function index()
    {
        try {
            $rows = (new BannerModel())->getActiveAndroidBanners();
            $banners = [];

            foreach ($rows as $row) {
                $id = (int)($row['id'] ?? 0);
                if ($id < 1) continue;

                $image = $row['gambar'] ?? null;
                if (!is_string($image) || $image === '') continue;

                $revision = substr(hash('sha256', $image), 0, 20);
                $banners[] = [
                    'id' => (string)$id,
                    'title' => trim((string)($row['judul'] ?? '')),
                    'alt' => trim((string)($row['alt'] ?? '')),
                    'image_url' => base_url('imgbanner/' . $id . '?v=' . $revision),
                    'link' => trim((string)($row['link'] ?? '')),
                    'order' => (int)($row['urutan'] ?? 0),
                ];
            }

            return $this->success(['banners' => $banners]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API public banner feed failed: ' . get_class($e));
            return $this->failure('BANNERS_UNAVAILABLE', 'Banner belum dapat dimuat.', 503);
        }
    }
}
