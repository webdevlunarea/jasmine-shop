<?php

namespace App\Controllers\Api\V1;

use App\Services\Notifications\CustomerMarketingPreferences as Preferences;
use Config\Database;

final class MarketingPreferencesController extends ApiController
{
    public function show()
    {
        try {
            return $this->success((new Preferences(Database::connect()))->get($this->authEmail()));
        } catch (\Throwable $error) {
            log_message('error', 'Customer API marketing preferences read failed: ' . get_class($error));
            return $this->failure('PREFERENCES_UNAVAILABLE', 'Preferensi promosi belum dapat dimuat.', 503);
        }
    }

    public function update()
    {
        $input = $this->payload();
        if (!array_key_exists('email_promotions', $input)
            || !in_array($input['email_promotions'], [true, false, 0, 1, '0', '1'], true)) {
            return $this->failure('VALIDATION_ERROR', 'Pilihan email promosi belum valid.', 422);
        }
        try {
            $saved = (new Preferences(Database::connect()))->update(
                $this->authEmail(),
                in_array($input['email_promotions'], [true, 1, '1'], true)
            );
            return $this->success($saved);
        } catch (\Throwable $error) {
            log_message('error', 'Customer API marketing preferences update failed: ' . get_class($error));
            return $this->failure('PREFERENCES_SAVE_FAILED', 'Preferensi belum tersimpan. Coba lagi.', 503);
        }
    }
}
