<?php

namespace App\Controllers;

use App\Models\KabupatenModel;
use App\Models\KecamatanModel;
use App\Models\ProvinsiModel;
use App\Models\KelurahanModel;


class NyolongController extends BaseController
{
    protected $provinsiModel;
    protected $kabupatenModel;
    // Tambahkan model untuk kecamatan jika diperlukan
    protected $kecamatanModel;
    protected $kelurahanModel;
    public function __construct()
    {
        // Constructor code here if needed
        $this->provinsiModel = new ProvinsiModel();
        $this->kabupatenModel = new KabupatenModel();
        // Inisialisasi model kecamatan jika diperlukan
        $this->kecamatanModel = new KecamatanModel();
        $this->kelurahanModel = new KelurahanModel();
    }
    public function index()
    {
        return view('pages/nyolong');
    }
    public function provinsi()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/province",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $data = json_decode($response, true);

        foreach ($data['rajaongkir']['results'] as $key => $value) {
            $this->provinsiModel->insert([
                'id' => $value['province_id'],
                'label' => $value['province']
            ]);
        }
        dd([
            'success' => true,
            'message' => 'Data provinsi berhasil disimpan.',
            'data' => $data['rajaongkir']['results']
        ]);
    }
    public function getProvinsi()
    {
        $data = $this->provinsiModel->findAll();
        return $this->response->setJSON($data);
    }
    public function kabupaten($provinsiId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/city?province=" . $provinsiId,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $data = json_decode($response, true);
        foreach ($data['rajaongkir']['results'] as  $value) {
            $this->kabupatenModel->insert([
                'id' => $value['city_id'],
                'provinsi_id' => $value['province_id'],
                'label' => $value['city_name']
            ]);
        }
        return $this->response->setJSON([
            'success' => true,
            'data' => $data['rajaongkir']['results'],
            'panjang' => count($data['rajaongkir']['results']),
        ]);
    }
    public function getKabupaten()
    {
        $data = $this->kabupatenModel->findAll();
        return $this->response->setJSON($data);
    }
    public function kecamatan($kabupatenId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://pro.rajaongkir.com/api/subdistrict?city=" . $kabupatenId,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "key: 6bc9315fb7a163e74a04f9f54ede3c2c"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        }
        $data = json_decode($response, true);
        foreach ($data['rajaongkir']['results'] as  $value) {
            // Simpan kecamatan ke database
            // Misalnya, jika Anda memiliki model KecamatanModel
            $this->kecamatanModel->insert([
                'id' => $value['subdistrict_id'],
                'provinsi_id' => $value['province_id'],
                'kabupaten_id' => $kabupatenId,
                'label' => $value['subdistrict_name']
            ]);
        }
        return $this->response->setJSON([
            'success' => true,
            'data' => $data['rajaongkir']['results'],
            'panjang' => count($data['rajaongkir']['results']),
        ]);
    }
    public function getKecamatan($pag)
    {
        $limit = 20;
        $offset = ($pag - 1) * $limit;
        $data = $this->kecamatanModel->limit($limit, $offset)->findAll();
        return $this->response->setJSON($data);
    }

    public function kelurahan($kec)
    {
        $kec = str_replace('/', ' ', $kec);

        $body = json_decode($this->request->getBody(), true);

        if (!$body || !isset($body['provinsi_id'], $body['kabupaten_id'], $body['kecamatan_id'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Body JSON tidak lengkap atau tidak valid',
                'received' => $body
            ]);
        }
        $apiUrl = "https://dakotacargo.co.id/api/api_glb_M_kodepos.asp?key=15f6a51696a8b034f9ce366a6dc22138&id=11022019000001&aKec=" . rawurlencode($kec);
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        // Jika cURL error
        if ($err) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'cURL Error: ' . $err
            ]);
        }

        $cleanResponse = iconv("UTF-8", "UTF-8//IGNORE", $response);
        $cleanResponse = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $cleanResponse);
        $cleanResponse = trim($cleanResponse);
        $kode = json_decode(str_replace('\\', "", $cleanResponse), true);
        // $kode = json_decode(str_replace('\\', "", $kode), true);

        // return $this->response->setJSON([
        //     'success' => true,
        //     'data' => $kode,
        //     'raw_response' => $cleanResponse,
        //     'api_url' => $apiUrl,
        // ]);

        // if (!is_array($kode)) {
        //     return $this->response->setStatusCode(502)->setJSON([
        //         'success' => false,
        //         'message' => 'Respons dari API Dakota tidak valid JSON',
        //         'raw_response' => json_decode($cleanResponse, true)
        //     ]);
        // }

        foreach ($kode as $value) {
            $existingKelurahan = $this->kelurahanModel->where([
                'provinsi_id' => $body['provinsi_id'],
                'kabupaten_id' => $body['kabupaten_id'],
                'kecamatan_id' => $body['kecamatan_id'],
                'label' => $value['DesaKelurahan']
            ])->first();

            if (!$existingKelurahan) {
                $this->kelurahanModel->insert([
                    'provinsi_id' => $body['provinsi_id'],
                    'kabupaten_id' => $body['kabupaten_id'],
                    'kecamatan_id' => $body['kecamatan_id'],
                    'label' => $value['DesaKelurahan'],
                    'kodepos' => $value['KodePos']
                ]);
            }
        }

        // Berikan respons berhasil
        return $this->response->setJSON([
            'success' => true,
            'data' => $kode,
            'panjang' => count($kode)
        ]);
    }

}