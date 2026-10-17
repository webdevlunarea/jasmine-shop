<?php

namespace App\Controllers;

use App\Models\BarangModel;
use App\Models\KonstantaModel;
use App\Models\StokModel;
use App\Services\Integrations\LunaCatalogSchema;
use App\Services\Integrations\LunaCatalogueClient;
use App\Services\Integrations\LunaMappingReview;
use App\Services\Integrations\LunaProductIdentity;

class LunaMappingAdmin extends BaseController
{
    private function allowed(): bool { return (bool)session('isLogin') && (string)session('role') === '1'; }

    public function index()
    {
        if (!$this->allowed()) return redirect()->to('/login');
        $model = new KonstantaModel();
        $rows = (new BarangModel())->select('id,nama,varian,active,stok')->orderBy('nama', 'ASC')->findAll();
        $all = []; $mapped = 0;
        foreach ($rows as $row) {
            try {
                $row['identity'] = $model->getLunaProductIdentity($row['id']);
                $row['variants'] = LunaMappingReview::variants($row);
                $row['revision'] = LunaMappingReview::revision($row, $row['identity']);
                $row['mapping_error'] = null;
                if ($row['identity']) $mapped++;
            } catch (\DomainException $e) { $row['mapping_error'] = $e->getMessage(); $row['identity'] = null; }
            $all[] = $row;
        }
        $id = $this->request->getGet('product');
        $selected = null;
        foreach ($all as $row) if ($row['id'] === $id) $selected = $row;
        $q = is_string($this->request->getGet('q')) ? mb_substr(trim($this->request->getGet('q')), 0, 100) : '';
        $pending = $this->request->getGet('pending') === '1';
        $filtered = array_values(array_filter($all, static fn($p) => (!$pending || !$p['identity']) && ($q === '' || mb_stripos($p['nama'].' '.$p['id'], $q) !== false)));
        $page = min(max(1, (int)$this->request->getGet('page')), max(1, (int)ceil(count($filtered) / 20)));
        return view('pages/lunaMappingAdmin', ['title'=>'Pemetaan MyLuna', 'products'=>array_slice($filtered, ($page - 1) * 20, 20),
            'selected'=>$selected, 'total'=>count($all), 'mapped'=>$mapped, 'q'=>$q, 'pending'=>$pending, 'page'=>$page, 'pages'=>max(1, (int)ceil(count($filtered)/20)),
            'msg'=>session()->getFlashdata('msg'), 'identityEnabled'=>filter_var(env('LUNA_PRODUCT_IDENTITY_ENABLED', false), FILTER_VALIDATE_BOOLEAN)]);
    }

    public function catalogue()
    {
        $this->response->setHeader('Cache-Control', 'no-store');
        if (!$this->allowed()) return $this->response->setStatusCode(403)->setJSON(['success'=>false]);
        try {
            $products = (new LunaCatalogueClient())->products();
            $owners = [];
            foreach ((new KonstantaModel())->like('label', LunaProductIdentity::PREFIX, 'after')->findAll() as $row) {
                $identity = json_decode((string)$row['value'], true);
                if (!is_array($identity) || !LunaProductIdentity::uuid($identity['product_id'] ?? null)) throw new \DomainException('Ada pemetaan tersimpan yang perlu diperiksa pengelola sistem.');
                $owners[$identity['product_id']][] = substr($row['label'], strlen(LunaProductIdentity::PREFIX));
            }
            foreach ($products as &$product) {
                $product['fingerprint'] = LunaMappingReview::fingerprint($product);
                $product['local_website_ids'] = $owners[$product['id']] ?? [];
            }
            unset($product);
            return $this->response->setJSON(['success'=>true, 'products'=>$products]);
        } catch (\DomainException $e) { return $this->response->setStatusCode(503)->setJSON(['success'=>false, 'message'=>$e->getMessage()]); }
    }

    public function save()
    {
        if (!$this->allowed() || strtolower($this->request->getMethod()) !== 'post') return $this->response->setStatusCode(403);
        \Config\Services::security()->verify($this->request);
        $id = $this->request->getPost('website_id');
        $target = '/lunamappingadmin';
        $db = null; $locked = false; $begun = false;
        try {
            if (!is_string($id) || !preg_match('/^[A-Za-z0-9_-]{1,15}$/D', $id)) throw new \DomainException('ID produk website tidak valid.');
            $target .= '?product=' . rawurlencode($id);
            $note = $this->request->getPost('review_note');
            if (is_string($note)) $note = trim(strip_tags($note));
            if ($this->request->getPost('confirmed') !== '1' || !is_string($note) || mb_strlen(trim($note)) < 10 || mb_strlen($note) > 500 || !session('email')) throw new \DomainException('Konfirmasi kecocokan dan isi catatan pemeriksaan minimal 10 karakter.');
            $masterId = $this->request->getPost('master_id');
            if (!is_string($masterId)) throw new \DomainException('Pilih produk MyLuna.');
            $master = (new LunaCatalogueClient())->products($masterId)[0];
            $fingerprint = $this->request->getPost('master_fingerprint');
            if (!is_string($fingerprint) || !hash_equals(LunaMappingReview::fingerprint($master), $fingerprint)) throw new \DomainException('Katalog MyLuna telah berubah. Muat ulang dan periksa kembali.');
            $selected = $this->request->getPost('variant_ids') ?? [];
            if (!is_array($selected)) throw new \DomainException('Pilihan varian tidak valid.');
            $db = db_connect();
            $lockName = 'lunarea_catalog_identity_' . substr(hash('sha256', $db->getDatabase()), 0, 24);
            $locked = (int)($db->query('SELECT GET_LOCK(?, 5) AS acquired', [$lockName])->getRowArray()['acquired'] ?? 0) === 1;
            if (!$locked) throw new \DomainException('Katalog sedang diproses. Coba kembali sesaat lagi.');
            if (!$db->transBegin()) throw new \RuntimeException('Transaction unavailable');
            $begun = true;
            $row = $db->query('SELECT id,nama,varian,active,stok FROM ' . $db->protectIdentifiers($db->prefixTable('barang')) . ' WHERE id = ? FOR UPDATE', [$id])->getRowArray();
            if (!$row) throw new \DomainException('Produk website sudah tidak tersedia.');
            $model = new KonstantaModel($db);
            $existing = $model->getLunaProductIdentity($id);
            $revision = $this->request->getPost('revision');
            if (!is_string($revision) || !hash_equals(LunaMappingReview::revision($row, $existing), $revision)) throw new \DomainException('Data produk atau pemetaan telah berubah. Muat ulang sebelum menyimpan.');
            $identity = LunaMappingReview::identity($row, $master, $selected, $existing);
            $identity['review'] = ['by'=>(string)session('email'), 'at'=>gmdate('c'), 'note'=>$note, 'master_fingerprint'=>$fingerprint];
            $stock = LunaMappingReview::stock($row, $master, $identity);
            if ((string)$row['stok'] !== $stock['value']) {
                LunaCatalogSchema::product($id, ['stok'=>$stock['value']]);
                if (!$db->table('barang')->where('id', $id)->update(['stok'=>$stock['value']])) throw new \RuntimeException('Stock mirror update failed');
                $history = new StokModel($db);
                $nameCapacity = $history->catalogNameCapacity();
                $oldStocks = array_map('intval', explode(',', (string)$row['stok']));
                foreach ($stock['entries'] as $index=>$entry) {
                    $previous = (int)($oldStocks[$index] ?? 0);
                    $current = (int)$entry['stock'];
                    if ($previous === $current) continue;
                    $record = [
                        'id'=>(string)random_int(1000000000, 2147483647),
                        'id_barang'=>$id,
                        'nama'=>(string)$row['nama'],
                        'varian'=>(string)$entry['variant'],
                        'jumlah'=>(string)($current - $previous),
                        'email_admin'=>'luna-system-sync',
                        'keterangan'=>'Sinkron stok awal dari MyLuna',
                        'stok_akhir'=>$current,
                        'tanggal'=>date('Y-m-d H:i:s'),
                    ];
                    LunaCatalogSchema::stock($record, $nameCapacity);
                    if (!$history->insert($record)) throw new \RuntimeException('Stock history insert failed');
                }
            }
            $model->saveLunaProductIdentity($id, $identity);
            if (!$db->transStatus() || !$db->transCommit()) throw new \RuntimeException('Commit failed');
            $begun = false;
            log_message('info', 'Admin menyimpan pemetaan MyLuna untuk produk {product}.', ['product'=>$id]);
            $message = 'Pasangan produk dan varian tersimpan. Stok Lunarea diselaraskan dari MyLuna; gambar, harga, ID, dan pesanan lama tetap utuh.';
        } catch (\DomainException $e) { $message = $e->getMessage(); }
        catch (\Throwable $e) { log_message('error', 'Luna mapping save failed: ' . get_class($e)); $message = 'Pemetaan belum tersimpan. Muat ulang lalu coba kembali.'; }
        finally {
            if ($db && $begun) $db->transRollback();
            if ($db && $locked) $db->query('SELECT RELEASE_LOCK(?)', [$lockName]);
        }
        return redirect()->to($target)->withCookies()->with('msg', $message);
    }
}
