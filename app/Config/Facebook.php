<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Facebook extends BaseConfig
{
    public bool $enabled = false;
    public string $appId = '';
    public string $appSecret = '';
    public string $redirectUri = '';
    public string $graphVersion = 'v25.0';

    public function ready(): bool
    {
        if (!$this->enabled || !preg_match('/^\d{1,64}$/D', $this->appId) || trim($this->appSecret) === ''
            || !preg_match('/^v\d+\.\d+$/D', $this->graphVersion)
            || !filter_var($this->redirectUri, FILTER_VALIDATE_URL)) return false;

        $uri = parse_url($this->redirectUri);
        if (isset($uri['user']) || isset($uri['pass']) || isset($uri['query']) || isset($uri['fragment'])
            || !preg_match('~/auth/facebook/callback$~D', $uri['path'] ?? '')) return false;

        // HTTP is only for local development; public callbacks must use TLS.
        return $uri['scheme'] === 'https' || ($uri['scheme'] === 'http'
            && in_array(strtolower($uri['host']), ['localhost', '127.0.0.1', '[::1]'], true));
    }
}
