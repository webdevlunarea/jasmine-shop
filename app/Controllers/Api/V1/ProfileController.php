<?php

namespace App\Controllers\Api\V1;

use App\Models\PembeliModel;
use App\Models\GambarUserModel;
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
        $photoPath = '/imguser/' . $encodedEmail . '?v=' . $revision;
        $photoUrl = site_url('imguser/' . $encodedEmail) . '?v=' . $revision;
        $db = db_connect();
        $db->transBegin();
        try {
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

        return $this->success(['customer' => ['photo_url' => $photoUrl]], ['message' => 'Foto profil berhasil diperbarui.']);
    }
}
