<?php

namespace App\Controllers;

use App\Services\Referrals\AndroidReferralProgram;

final class AndroidReferralLink extends BaseController
{
    public function show(string $code)
    {
        try {
            $program = new AndroidReferralProgram();
            if (!$program->publicCode($code)) return $this->response->setStatusCode(404)->setBody('Tautan afiliasi tidak tersedia.');
            $code = strtoupper($code);
            session()->set('affiliate_referral_code', $code);
            $slug = strtolower(trim((string)$this->request->getGet('product')));
            $product = null;
            $productId = trim((string)$this->request->getGet('product_id'));
            if ($productId !== '' && preg_match('/^[A-Za-z0-9_-]{1,64}$/', $productId)) {
                $product = (new \App\Models\BarangModel())->where(['id' => $productId, 'active' => '1'])->first();
                if (is_array($product)) $slug = strtolower(trim((string)($product['path'] ?? '')));
            }
            if ($slug !== '' && preg_match('/^[a-z0-9-]{1,120}$/', $slug)) {
                $product ??= (new \App\Models\BarangModel())->where(['path' => $slug, 'active' => '1'])->first();
            }
            $settings = $program->settings();
            $productName = is_array($product) ? trim(strip_tags((string)($product['nama'] ?? ''))) : '';
            $productImageUrl = is_array($product) && !empty($product['id'])
                ? base_url('imgshare/' . rawurlencode((string)$product['id'])) . '?v=' . substr(hash('sha256', (string)($product['gambar'] ?? '')), 0, 16)
                : base_url('img/logo%20icon.png');
            $productUrl = is_array($product)
                ? base_url('product/' . rawurlencode($slug) . '?ref=' . rawurlencode($code))
                : base_url('/all');
            $userAgent = $this->request->getUserAgent()->getAgentString();
            $isLinkPreviewBot = preg_match('/facebookexternalhit|WhatsApp|Twitterbot|TelegramBot|Slackbot|Discordbot|LinkedInBot|Google-InspectionTool|Googlebot|bingbot/i', $userAgent) === 1;
            if (is_array($product) && !$isLinkPreviewBot) {
                return redirect()->to($productUrl);
            }
            return view('pages/androidReferralLink', [
                'code' => $code,
                'title' => $productName !== '' ? $productName . ' | Lunarea Furniture' : 'Afiliasi Lunarea Furniture',
                'productName' => $productName,
                'productImageUrl' => $productImageUrl,
                'productUrl' => $productUrl,
                'sharePageUrl' => current_url() . '?' . http_build_query($this->request->getGet()),
                'commissionPercent' => (float)$settings['commission_percent'],
                'minimumSubtotal' => (int)$settings['minimum_subtotal'],
                'browseUrl' => base_url('/all'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Android referral link resolution failed: ' . get_class($e));
            return $this->response->setStatusCode(503)->setBody('Tautan ajak teman sedang tidak tersedia.');
        }
    }
}
