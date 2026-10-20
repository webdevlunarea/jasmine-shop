<?php

namespace App\Controllers\Api\V1;

use App\Services\Auth\CustomerUsernames;

final class UsernameController extends ApiController
{
    public function show()
    {
        try {
            $db = db_connect();
            if (!$db->tableExists('customer_usernames')) {
                return $this->failure('USERNAME_UNAVAILABLE', 'Pengaturan username belum tersedia.', 503);
            }
            return $this->success(['username' => (new CustomerUsernames($db))->settings($this->authEmail())]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API username settings unavailable: ' . get_class($e));
            return $this->failure('USERNAME_UNAVAILABLE', 'Pengaturan username belum dapat dimuat. Coba lagi nanti.', 503);
        }
    }

    public function save()
    {
        $username = $this->payload()['username'] ?? null;
        try {
            $service = new CustomerUsernames();
            $saved = $service->save($this->authEmail(), $username);
            return $this->success([
                'username' => $service->settings($this->authEmail()),
            ], ['message' => 'Username berhasil disimpan: ' . $saved]);
        } catch (\DomainException $e) {
            return $this->failure('USERNAME_NOT_ACCEPTED', $e->getMessage(), 422);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API username save failed: ' . get_class($e));
            return $this->failure('USERNAME_UNAVAILABLE', 'Username belum dapat disimpan. Coba lagi nanti.', 503);
        }
    }
}
