<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/** reCAPTCHA v2 checkbox / SiteVerify. Activation is explicit; never embed secrets in views. */
class Recaptcha extends BaseConfig
{
    public bool $enabled = false;
    public string $siteKey = '';
    public string $secretKey = '';
    public string $allowedHostnames = 'lunareafurniture.com,www.lunareafurniture.com';
}
