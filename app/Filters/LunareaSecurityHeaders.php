<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Adds baseline browser headers without weakening route-specific policy. */
final class LunareaSecurityHeaders implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        foreach ([
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'X-Download-Options' => 'noopen',
            'X-Permitted-Cross-Domain-Policies' => 'none',
        ] as $name => $value) {
            if ($response->getHeaderLine($name) === '') $response->setHeader($name, $value);
        }

        if ($response->getHeaderLine('Referrer-Policy') === '') {
            $response->setHeader('Referrer-Policy', 'same-origin');
        }
    }
}
