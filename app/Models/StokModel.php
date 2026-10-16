<?php

namespace App\Models;

use CodeIgniter\Model;

class StokModel extends Model
{
    private ?int $catalogNameCapacity = null;

    public function catalogNameCapacity(): int
    {
        if ($this->catalogNameCapacity !== null) return $this->catalogNameCapacity;
        $column = $this->db->query(
            'SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$this->db->prefixTable($this->table), 'nama']
        )->getRowArray();
        if (!$column || strtolower((string)$column['DATA_TYPE']) !== 'varchar'
            || !is_numeric($column['CHARACTER_MAXIMUM_LENGTH']) || (int)$column['CHARACTER_MAXIMUM_LENGTH'] < 1) {
            throw new \DomainException('Schema nama riwayat stok belum terverifikasi.');
        }
        return $this->catalogNameCapacity = min(100, (int)$column['CHARACTER_MAXIMUM_LENGTH']);
    }

    protected $table = 'stok';
    protected $allowedFields = [
        'id',
        'id_barang',
        'nama',
        'varian',
        'jumlah',
        'email_admin',
        'keterangan',
        'stok_akhir',
        'tanggal'
    ];
}
