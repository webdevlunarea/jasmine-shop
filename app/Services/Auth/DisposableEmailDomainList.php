<?php

namespace App\Services\Auth;

use RuntimeException;

/** Locally cached community blocklist; signup requests never call an external service. */
final class DisposableEmailDomainList
{
    public const SOURCE_URL = 'https://disposable.github.io/disposable-email-domains/domains_mx.txt';

    private static array $processCache = [];

    public function __construct(private ?string $path = null, private $fetch = null)
    {
        $this->path ??= WRITEPATH . 'cache/disposable-email-domains.txt';
    }

    public function isListed(string $domain): bool
    {
        $domains = $this->loadDomains();
        $candidate = strtolower(rtrim($domain, '.'));
        while ($candidate !== '' && str_contains($candidate, '.')) {
            if (isset($domains[$candidate])) return true;
            $candidate = substr($candidate, strpos($candidate, '.') + 1);
        }
        return false;
    }

    /** Fetch, validate and atomically replace the local list. */
    public function refresh(): int
    {
        if ($this->fetch) {
            $body = ($this->fetch)(self::SOURCE_URL);
        } else {
            $response = service('curlrequest')->get(self::SOURCE_URL, [
                'timeout' => 20,
                'connect_timeout' => 7,
                'verify' => true,
                'allow_redirects' => false,
                'headers' => ['Accept' => 'text/plain'],
            ]);
            if ($response->getStatusCode() !== 200) throw new RuntimeException('Sumber daftar domain tidak merespons.');
            $body = $response->getBody();
        }

        if (!is_string($body) || $body === '' || strlen($body) > 8 * 1024 * 1024) {
            throw new RuntimeException('Daftar domain sementara tidak valid.');
        }

        $domains = [];
        foreach (preg_split('/\r\n|\n|\r/', $body) as $line) {
            $domain = strtolower(trim($line));
            if ($domain === '' || str_starts_with($domain, '#')) continue;
            if (strlen($domain) > 253 || !preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/D', $domain)) {
                throw new RuntimeException('Daftar domain sementara berisi baris yang tidak valid.');
            }
            $domains[$domain] = true;
        }

        if (count($domains) < 1000) throw new RuntimeException('Daftar domain sementara terlalu pendek; daftar lama tetap digunakan.');
        $directory = dirname($this->path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Folder daftar domain sementara tidak tersedia.');
        }

        $temporaryPath = $this->path . '.' . bin2hex(random_bytes(6)) . '.tmp';
        $contents = implode("\n", array_keys($domains)) . "\n";
        if (file_put_contents($temporaryPath, $contents, LOCK_EX) === false || !rename($temporaryPath, $this->path)) {
            @unlink($temporaryPath);
            throw new RuntimeException('Daftar domain sementara tidak dapat disimpan.');
        }

        clearstatcache(true, $this->path);
        unset(self::$processCache[$this->path]);
        return count($domains);
    }

    private function loadDomains(): array
    {
        clearstatcache(true, $this->path);
        $mtime = is_file($this->path) ? (int) filemtime($this->path) : 0;
        if (isset(self::$processCache[$this->path]) && self::$processCache[$this->path]['mtime'] === $mtime) {
            return self::$processCache[$this->path]['domains'];
        }

        $domains = [];
        if ($mtime > 0) {
            $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines !== false) {
                foreach ($lines as $line) {
                    $domain = strtolower(trim($line));
                    if ($domain !== '') $domains[$domain] = true;
                }
            }
        }
        self::$processCache[$this->path] = ['mtime' => $mtime, 'domains' => $domains];
        return $domains;
    }
}
