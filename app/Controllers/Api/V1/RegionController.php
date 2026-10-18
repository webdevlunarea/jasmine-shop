<?php

namespace App\Controllers\Api\V1;

use Config\Database;

class RegionController extends ApiController
{
    public function provinces()
    {
        return $this->regionList('provinsi', ['id', 'label'], []);
    }

    public function cities()
    {
        return $this->regionList('kabupaten', ['id', 'provinsi_id', 'label'], ['provinsi_id' => (int)$this->request->getGet('province_id')]);
    }

    public function districts()
    {
        return $this->regionList('kecamatan', ['id', 'provinsi_id', 'kabupaten_id', 'label'], ['kabupaten_id' => (int)$this->request->getGet('city_id')]);
    }

    public function villages()
    {
        return $this->regionList('kelurahan', ['id', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kodepos', 'label'], ['kecamatan_id' => (int)$this->request->getGet('district_id')]);
    }

    private function regionList(string $table, array $columns, array $filter)
    {
        if ($filter && reset($filter) <= 0) return $this->failure('VALIDATION_ERROR', 'ID wilayah wajib dipilih.', 422);
        $db = Database::connect();
        if (!$db->tableExists($table)) return $this->failure('REGION_DATA_UNAVAILABLE', 'Data wilayah sementara tidak tersedia.', 503);
        $builder = $db->table($table)->select(implode(',', $columns))->orderBy('label', 'ASC');
        foreach ($filter as $field => $value) $builder->where($field, $value);
        return $this->success(['regions' => $builder->get()->getResultArray()]);
    }
}
