<?php

namespace App\Services\Notifications;

interface FirebaseMessageSender
{
    public function send(string $deviceToken, array $notification, array $data = []): array;
}
