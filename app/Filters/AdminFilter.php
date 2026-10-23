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
        // Do something here
    }
}
