<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/** Keep the future mobile API dark until explicitly enabled in deployment config. */
final class ApiVersionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (filter_var(env('LUNAREA_ANDROID_API_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) return;
        return service('response')->setStatusCode(503)->setJSON([
            'success' => false,
            'error' => ['code' => 'API_NOT_ENABLED', 'message' => 'Layanan aplikasi belum diaktifkan.'],
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
