<?php

namespace App\Filters;

use App\Services\Auth\AdminPermissions;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session("isLogin")) {
            return redirect()->to(site_url('login'));
        }
        if (session("role") != "1") {
            return redirect()->to(site_url('/'));
        }

        $permission = AdminPermissions::requiredPermission($request->getUri()->getPath(), $request->getMethod());
        if (!AdminPermissions::canAccess(session('admin_role') ?: 'ADMIN', $permission, (array) session('admin_permissions'))) {
            return service('response')->setStatusCode(403)->setBody('Akses ditolak untuk peran administrator Anda.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ((string) session('role') !== '1') return;
        $method = strtoupper($request->getMethod());
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true)) return;

        try {
            $db = db_connect();
            if (!$db->tableExists('admin_activity_logs')) return;
            $db->table('admin_activity_logs')->insert([
                'actor_email' => (string) session('email'),
                'admin_role' => (string) (session('admin_role') ?: 'ADMIN'),
                'http_method' => $method,
                'route' => substr($request->getUri()->getPath(), 0, 255),
                'response_code' => $response->getStatusCode(),
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $error) {
            log_message('error', 'Admin activity audit write failed: {type}', ['type' => get_class($error)]);
        }
    }
}
