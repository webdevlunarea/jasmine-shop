<?php

namespace App\Controllers\Api\V1;

use App\Models\PembeliModel;
use App\Models\GambarUserModel;
use App\Services\Auth\IndonesianMobileNumber;

final class ProfileController extends ApiController
{
    private const PHOTO_CHANGE_INTERVAL = 604800;

    public function update()
    {
        $input = $this->payload();
        $name = trim((string)($input['name'] ?? ''));
        $phone = (new IndonesianMobileNumber())->normalize((string)($input['phone'] ?? ''));
        $gender = array_key_exists('gender', $input) && is_string($input['gender'])
            ? strtolower(trim($input['gender'])) : null;
        if ($name === '' || mb_strlen($name) > 120 || $phone === null) {
            return $this->failure('VALIDATION_ERROR', 'Nama atau nomor ponsel belum valid.', 422);
        }
        if (array_key_exists('gender', $input) && !in_array($gender, ['pria', 'wanita'], true)) {
            return $this->failure('VALIDATION_ERROR', 'Pilih salah satu jenis kelamin yang tersedia.', 422);
        }

        $email = $this->authEmail();
        $db = db_connect();
        $genderSchemaReady = $db->fieldExists('gender', 'pembeli') && $db->fieldExists('gender_changed_at', 'pembeli');
        if (!$genderSchemaReady && array_key_exists('gender', $input)) {
            return $this->failure('PROFILE_SCHEMA_UNAVAILABLE', 'Pengaturan profil belum siap. Coba lagi nanti.', 503);
        }
        if (!$genderSchemaReady) {
            $saved = (new PembeliModel())->where('email_user', $email)->set(['nama' => $name, 'nohp' => $phone])->update();
            if (!$saved) return $this->failure('PROFILE_SAVE_FAILED', 'Profil belum tersimpan. Coba lagi.', 500);
            return $this->success(['customer' => ['name' => $name, 'phone' => $phone, 'gender' => '']]);
        }
        $db->transBegin();
        try {
            $sql = $db->table('pembeli')->select('gender, gender_changed_at')->where('email_user', $email)->getCompiledSelect();
            $current = $db->query($sql . ($db->DBDriver === 'SQLite3' ? '' : ' FOR UPDATE'))->getRowArray();
            if (!$current) throw new \RuntimeException('Customer profile row is missing.');
            $changes = ['nama' => $name, 'nohp' => $phone];
            $savedGender = $current['gender'] ?? null;
            $changedAt = $current['gender_changed_at'] ?? null;
            if ($gender !== null && $gender !== $savedGender) {
                if ($savedGender !== null && $changedAt) {
                    $lastChanged = strtotime((string)$changedAt . ' UTC');
                    $availableAt = $lastChanged === false ? 0 : strtotime('+6 months', $lastChanged);
                    if ($availableAt > time()) {
                        $db->transRollback();
                        return $this->failure(
                            'GENDER_CHANGE_COOLDOWN',
                            'Jenis kelamin hanya dapat diubah setiap 6 bulan. Anda dapat mengubahnya kembali pada ' .
                                (new \DateTimeImmutable('@' . $availableAt))->setTimezone(new \DateTimeZone('Asia/Jakarta'))->format('d-m-Y H:i') . ' WIB.',
                            429,
                            ['gender_change_available_at' => gmdate('c', $availableAt)]
                        );
                    }
                }
                $changes['gender'] = $gender;
                $changes['gender_changed_at'] = gmdate('Y-m-d H:i:s');
                $savedGender = $gender;
                $changedAt = $changes['gender_changed_at'];
            }
            $saved = (new PembeliModel())->where('email_user', $email)->set($changes)->update();
            if ($saved === false || $db->transStatus() === false) throw new \RuntimeException('Customer profile update failed.');
            $db->transCommit();
            $availableAt = $changedAt ? strtotime('+6 months', strtotime((string)$changedAt . ' UTC')) : false;
            return $this->success(['customer' => [
                'name' => $name, 'phone' => $phone, 'gender' => $savedGender,
                'gender_change_available_at' => $availableAt ? gmdate('c', $availableAt) : null,
            ]]);
        } catch (\Throwable $error) {
            $db->transRollback();
            log_message('error', 'Customer profile update failed: ' . get_class($error));
            return $this->failure('PROFILE_SAVE_FAILED', 'Profil belum tersimpan. Coba lagi.', 500);
        }
    }

    public function uploadPhoto()
    {
        $photo = $this->request->getFile('photo');
        if (!$photo || !$photo->isValid() || $photo->hasMoved()) {
            return $this->failure('INVALID_PHOTO', 'Pilih foto profil yang valid.', 422);
        }
        if ($photo->getSize() > 2 * 1024 * 1024) {
            return $this->failure('PHOTO_TOO_LARGE', 'Ukuran foto maksimal 2 MB.', 422);
        }

        $bytes = file_get_contents($photo->getTempName());
        if (!is_string($bytes) || $bytes === '') {
            return $this->failure('INVALID_PHOTO', 'Foto belum dapat dibaca. Pilih file lain.', 422);
        }
        $image = @getimagesizefromstring($bytes);
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $mime = is_array($image) ? (string)($image['mime'] ?? '') : '';
        $uploadedMime = (string)$photo->getMimeType();
        if (!in_array($mime, $allowedTypes, true) || $mime !== $uploadedMime) {
            return $this->failure('UNSUPPORTED_PHOTO', 'Gunakan foto JPG, PNG, atau WebP yang valid.', 422);
        }
        $width = (int)($image[0] ?? 0);
        $height = (int)($image[1] ?? 0);
        if ($width < 1 || $height < 1 || $width > 6000 || $height > 6000 || $width * $height > 24000000) {
            return $this->failure('PHOTO_DIMENSIONS_INVALID', 'Resolusi foto terlalu besar. Pilih foto lain.', 422);
        }

        $email = $this->authEmail();
        $encodedEmail = rtrim(strtr(base64_encode($email), '+/', '-_'), '=');
        // Content-addressed versioning prevents Android/Cloudflare caches from
        // showing an older avatar after a successful replacement.
        $revision = substr(hash('sha256', $bytes), 0, 24);
        $db = db_connect();
        $db->transBegin();
        try {
            // Serialize concurrent uploads for this customer and enforce the
            // cooldown on the server, not just in the Android UI.
            $profile = $db->query('SELECT foto FROM pembeli WHERE email_user = ? FOR UPDATE', [$email])->getRowArray();
            if (!$profile) throw new \RuntimeException('Customer profile row is missing.');
            $lastChangedAt = null;
            if (preg_match('/(?:\\?|&)at=(\\d{10})(?:&|$)/', (string)($profile['foto'] ?? ''), $match)) {
                $lastChangedAt = (int)$match[1];
            }
            $now = time();
            if ($lastChangedAt !== null && $now < $lastChangedAt + self::PHOTO_CHANGE_INTERVAL) {
                $db->transRollback();
                $availableAt = $lastChangedAt + self::PHOTO_CHANGE_INTERVAL;
                $availableLabel = (new \DateTimeImmutable('@' . $availableAt))
                    ->setTimezone(new \DateTimeZone('Asia/Jakarta'))
                    ->format('d-m-Y H:i') . ' WIB';
                return $this->failure(
                    'PHOTO_CHANGE_COOLDOWN',
                    'Foto profil hanya dapat diganti setiap 7 hari. Anda dapat menggantinya kembali pada ' . $availableLabel . '.',
                    429,
                    ['photo_change_available_at' => gmdate('c', $availableAt)]
                );
            }

            $photoPath = '/imguser/' . $encodedEmail . '?v=' . $revision . '&at=' . $now;
            $photoUrl = site_url('imguser/' . $encodedEmail) . '?v=' . $revision;
            $images = new GambarUserModel();
            if ($images->getGambar($email)) {
                $imageSaved = $images->where('email_user', $email)->set(['gambar' => $bytes])->update();
            } else {
                $imageSaved = $images->insert(['email_user' => $email, 'gambar' => $bytes]);
            }
            if ($imageSaved === false) throw new \RuntimeException('Profile image row was not saved.');
            $profileSaved = (new PembeliModel())->where('email_user', $email)->set(['foto' => $photoPath])->update();
            if ($profileSaved === false) throw new \RuntimeException('Profile photo URL was not saved.');
            if ($db->transStatus() === false) throw new \RuntimeException('Profile photo transaction failed.');
            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'Customer profile photo update failed: ' . get_class($e));
            return $this->failure('PHOTO_SAVE_FAILED', 'Foto profil belum dapat disimpan. Coba lagi.', 500);
        }

        $availableAt = gmdate('c', $now + self::PHOTO_CHANGE_INTERVAL);
        return $this->success(['customer' => [
            'photo_url' => $photoUrl,
            'photo_change_available_at' => $availableAt,
        ]], ['message' => 'Foto profil berhasil diperbarui.']);
    }
}
