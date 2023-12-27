<?php

namespace App\Models;

use CodeIgniter\Model;

class GambarBarangModel extends Model
{
    protected $table = 'gambar_barang';
    protected $allowedFields = [
        'id',
        'gambar1',
        'gambar2',
        'gambar3',
        'gambar4',
        'gambar5',
        'gambar6',
        'gambar7',
        'gambar8',
        'gambar9',
        'gambar10',
        'gambar11',
        'gambar12',
        'gambar13',
        'gambar14',
        'gambar15',
        'gambar16',
        'gambar17',
        'gambar18',
        'gambar19',
        'gambar20',
        'gambar21',
        'gambar22',
        'gambar23',
        'gambar24',
        'gambar25',
        'gambar26',
        'gambar27',
        'gambar28',
        'gambar29',
        'gambar30',
        'gambar31',
        'gambar32',
        'gambar33',
        'gambar34',
        'gambar35',
        'gambar36',
        'gambar37',
        'gambar38',
        'gambar39',
        'gambar40',
        'gambar41',
        'gambar42',
        'gambar43',
        'gambar44',
        'gambar45',
        'gambar46',
        'gambar47',
        'gambar48',
        'gambar49',
        'gambar50',
    ];

    public function getGambar($id = false)
    {
        if ($id == false) {
            return $this->findAll();
        }
        return $this->where(['id' => $id])->first();
    }
    public function getGambarLimit()
    {
        return $this->findAll(10, 0);
    }
}
