<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/** Website SiteVerify plus native Android Enterprise assessment settings. */
class Recaptcha extends BaseConfig
{
    public bool $enabled;
    public string $siteKey;
    public string $secretKey;
    public string $allowedHostnames;

    public function __construct()
    {
        parent::__construct();
        // Production defaults to fail-closed; configure valid keys before rollout.
        $this->enabled = filter_var(env('RECAPTCHA_ENABLED', ENVIRONMENT === 'production'), FILTER_VALIDATE_BOOLEAN);
        $this->siteKey = (string) env('RECAPTCHA_SITE_KEY', '');
        $this->secretKey = (string) env('RECAPTCHA_SECRET_KEY', '');
        $this->allowedHostnames = (string) env('RECAPTCHA_ALLOWED_HOSTNAMES', 'lunareafurniture.com,www.lunareafurniture.com');
    }

    /** Native API checks are explicitly enabled independently from website CAPTCHA. */
    public bool $androidEnabled = true;
    public string $androidProjectId = 'adityaanugrah-1695739714172';
    public string $androidSiteKey = '6LdhT_YtAAAAAPXNKQu3-4owL1D6Yb97D4qZzIW_';
    public string $androidPackageName = 'com.lunareafurniture.lunarea';
    public string $androidServiceAccountPath = '/etc/lunarea/firebase-service-account.json';
    public float $androidMinimumScore = 0.5;
}
