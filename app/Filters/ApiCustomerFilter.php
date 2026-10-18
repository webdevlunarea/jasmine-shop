<?php

namespace App\Filters;

use App\Services\Auth\CustomerApiTokens;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiCustomerFilter implements FilterInterface
{
    protected function authenticateToken(string $token): ?array
    {
        return (new CustomerApiTokens())->authenticate($token);
    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $header = trim($request->getHeaderLine('Authorization'));
        if (!preg_match('/^Bearer\s+([A-Za-z0-9_-]{40,100})$/i', $header, $matches)) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Sesi aplikasi berakhir. Silakan masuk kembali.'],
            ]);
        }

        try {
            $customer = $this->authenticateToken($matches[1]);
        } catch (\Throwable $e) {
            log_message('error', 'Customer API authentication storage unavailable: ' . get_class($e));
            return service('response')->setStatusCode(503)->setJSON([
                'success' => false,
                'error' => ['code' => 'AUTH_UNAVAILABLE', 'message' => 'Layanan akun sementara terganggu. Coba lagi nanti.'],
            ]);
        }
        if (!$customer) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Sesi aplikasi berakhir. Silakan masuk kembali.'],
            ]);
        }
        $request->setHeader('X-Lunarea-Authenticated-Customer', (string)$customer['email']);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
