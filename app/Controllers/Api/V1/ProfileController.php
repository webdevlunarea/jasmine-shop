<?php

namespace App\Controllers\Api\V1;

use App\Models\PembeliModel;
use App\Services\Auth\IndonesianMobileNumber;

final class ProfileController extends ApiController
{
    public function update()
    {
        $input = $this->payload();
        $name = trim((string)($input['name'] ?? ''));
        $phone = (new IndonesianMobileNumber())->normalize((string)($input['phone'] ?? ''));
        if ($name === '' || mb_strlen($name) > 120 || $phone === null) {
            return $this->failure('VALIDATION_ERROR', 'Nama atau nomor ponsel belum valid.', 422);
        }
        $model = new PembeliModel();
        $saved = $model->where('email_user', $this->authEmail())->set(['nama' => $name, 'nohp' => $phone])->update();
        if (!$saved) return $this->failure('PROFILE_SAVE_FAILED', 'Profil belum tersimpan. Coba lagi.', 500);
        return $this->success(['customer' => ['name' => $name, 'phone' => $phone]]);
    }
}
