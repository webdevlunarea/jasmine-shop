<?php

namespace App\Services\Auth;

/** Sanitized diagnostic identifiers only; no original exception/SQL/provider payload attached. */
final class FacebookAccountException extends \RuntimeException
{
    private string $stage;
    private int $databaseCode;

    public function __construct(string $stage, int $databaseCode = 0)
    {
        parent::__construct('Facebook account creation failed');
        $this->stage = in_array($stage, ['transaction_start', 'user_insert', 'buyer_insert', 'identity_insert', 'commit'], true) ? $stage : 'unknown';
        $this->databaseCode = $databaseCode;
    }

    public function stage(): string { return $this->stage; }
    public function databaseCode(): int { return $this->databaseCode; }
}
