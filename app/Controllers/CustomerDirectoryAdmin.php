<?php

namespace App\Controllers;

use CodeIgniter\Database\BaseBuilder;

class CustomerDirectoryAdmin extends BaseController
{
    public function requestDetail()
    {
        $email = $this->request->getPost('email');
        if (!is_string($email) || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            session()->setFlashdata('msg', 'Data pelanggan tidak ditemukan.');
            return redirect()->to(site_url('customerdirectoryadmin'));
        }
        session()->setFlashdata('customer_directory_detail_email', strtolower(trim($email)));
        return redirect()->to(site_url('customerdirectoryadmin/detail'));
    }

    public function detail()
    {
        $email = session()->getFlashdata('customer_directory_detail_email');
        $db = db_connect();
        if (!is_string($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !$db->tableExists('user') || !$db->tableExists('pembeli')
            || !$db->fieldExists('registration_method', 'user') || !$db->fieldExists('registered_at', 'user')) {
            session()->setFlashdata('msg', 'Data pelanggan tidak ditemukan atau sudah kedaluwarsa.');
            return redirect()->to(site_url('customerdirectoryadmin'));
        }

        $query = $db->table('user u')->select('u.email, u.active, u.registration_method, u.registered_at, p.nama, p.nohp')
            ->join('pembeli p', 'p.email_user = u.email', 'left')->where('u.email', $email)->where('u.role', '0');
        if ($db->fieldExists('admin_role', 'user')) $query->groupStart()->where('u.admin_role', null)->orWhere('u.admin_role', '')->groupEnd();
        $customer = $query->get()->getRowArray();
        if (!$customer) {
            session()->setFlashdata('msg', 'Data pelanggan tidak ditemukan.');
            return redirect()->to(site_url('customerdirectoryadmin'));
        }

        return view('pages/customerDirectoryDetailAdmin', [
            'title' => 'Detail pelanggan', 'customer' => $customer,
        ]);
    }

    private function customerBuilder(string $method, string $status, string $query, bool $hasAdminRole): BaseBuilder
    {
        $db = db_connect();
        $builder = $db->table('user u')
            ->select('u.email, u.active, u.registration_method, u.registered_at, p.nama, p.nohp')
            ->join('pembeli p', 'p.email_user = u.email', 'left')
            ->where('u.role', '0');

        // The directory is customer-only, including databases that use admin_role
        // for internal accounts alongside the legacy numeric role field.
        if ($hasAdminRole) {
            $builder->groupStart()->where('u.admin_role', null)->orWhere('u.admin_role', '')->groupEnd();
        }

        if ($method !== 'all') {
            if ($method === 'unknown') $builder->groupStart()->where('u.registration_method', null)->orWhereNotIn('u.registration_method', ['email', 'google', 'facebook'])->groupEnd();
            else $builder->where('u.registration_method', $method);
        }
        if ($status === 'active') $builder->where('u.active', '1');
        elseif ($status === 'pending') $builder->where('u.active', '0');
        if ($query !== '') {
            $builder->groupStart()
                ->like('u.email', $query)
                ->orLike('p.nama', $query)
                ->orLike('p.nohp', $query)
                ->groupEnd();
        }
        return $builder;
    }

    public function index()
    {
        $db = db_connect();
        foreach (['user', 'pembeli'] as $table) {
            if (!$db->tableExists($table)) {
                return view('pages/customerDirectoryAdmin', [
                    'title' => 'Direktori pengguna', 'ready' => false,
                    'customers' => [], 'total' => 0, 'page' => 1, 'perPage' => 25,
                    'pages' => 1, 'method' => 'all', 'status' => 'active', 'query' => '', 'methodCounts' => [],
                ]);
            }
        }
        foreach (['registration_method', 'registered_at', 'active'] as $field) {
            if (!$db->fieldExists($field, 'user')) {
                return view('pages/customerDirectoryAdmin', [
                    'title' => 'Direktori pengguna', 'ready' => false,
                    'customers' => [], 'total' => 0, 'page' => 1, 'perPage' => 25,
                    'pages' => 1, 'method' => 'all', 'status' => 'active', 'query' => '', 'methodCounts' => [],
                ]);
            }
        }

        $hasAdminRole = $db->fieldExists('admin_role', 'user');
        $method = (string)$this->request->getGet('method');
        $status = (string)$this->request->getGet('status');
        $query = trim((string)$this->request->getGet('q'));
        if (!in_array($method, ['all', 'email', 'google', 'facebook', 'unknown'], true)) $method = 'all';
        if (!in_array($status, ['active', 'pending', 'all'], true)) $status = 'active';
        $query = mb_substr($query, 0, 100);
        $perPage = 25;
        $total = $this->customerBuilder($method, $status, $query, $hasAdminRole)->countAllResults();
        $pages = max(1, (int)ceil($total / $perPage));
        $page = min($pages, max(1, (int)$this->request->getGet('page')));
        $customers = $this->customerBuilder($method, $status, $query, $hasAdminRole)
            ->orderBy('u.registered_at', 'DESC')->orderBy('u.email', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();
        $countsBuilder = $db->table('user')->select('registration_method, COUNT(*) AS total')
            ->where('role', '0')->where('active', '1');
        if ($hasAdminRole) $countsBuilder->groupStart()->where('admin_role', null)->orWhere('admin_role', '')->groupEnd();
        $counts = $countsBuilder->groupBy('registration_method')->get()->getResultArray();
        $methodCounts = ['email' => 0, 'google' => 0, 'facebook' => 0, 'unknown' => 0];
        foreach ($counts as $count) {
            $key = in_array($count['registration_method'], ['email', 'google', 'facebook'], true)
                ? $count['registration_method'] : 'unknown';
            $methodCounts[$key] += (int)$count['total'];
        }
        return view('pages/customerDirectoryAdmin', [
            'title' => 'Direktori pengguna', 'ready' => true, 'customers' => $customers,
            'total' => $total, 'page' => $page, 'perPage' => $perPage, 'pages' => $pages,
            'method' => $method, 'status' => $status, 'query' => $query, 'methodCounts' => $methodCounts,
            'msg' => session()->getFlashdata('msg'),
        ]);
    }
}
