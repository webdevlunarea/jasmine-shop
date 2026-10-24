<?php

namespace App\Controllers;

use App\Services\Auth\AdminPermissions;
use DateTimeImmutable;
use DateTimeZone;

final class AdminActivityLogs extends BaseController
{
    private const PER_PAGE = 50;

    public function index()
    {
        $role = AdminPermissions::normalizeRole(session('admin_role') ?: 'ADMIN');
        $permissions = (array) session('admin_permissions');
        if ((string)session('role') !== '1' || !AdminPermissions::canAccess($role, 'settings.audit.read', $permissions)) {
            return $this->response->setStatusCode(403)->setBody('Akses tidak tersedia.');
        }

        $filters = [
            'from' => $this->validDate($this->getScalar('from')),
            'to' => $this->validDate($this->getScalar('to')),
            'actor' => mb_substr(trim($this->getScalar('actor')), 0, 120),
            'route' => mb_substr(trim($this->getScalar('route')), 0, 100),
            'method' => strtoupper(trim($this->getScalar('method'))),
        ];
        $errors = [];
        if ($this->getScalar('from') !== '' && $filters['from'] === null) $errors[] = 'Tanggal awal tidak valid.';
        if ($this->getScalar('to') !== '' && $filters['to'] === null) $errors[] = 'Tanggal akhir tidak valid.';
        if ($filters['from'] && $filters['to'] && $filters['from'] > $filters['to']) $errors[] = 'Tanggal awal harus sebelum tanggal akhir.';
        if (!in_array($filters['method'], ['', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $filters['method'] = '';
            $errors[] = 'Jenis tindakan tidak dikenali.';
        }

        $db = db_connect();
        $ready = $db->tableExists('admin_activity_logs');
        $rows = [];
        $total = 0;
        $page = max(1, (int)$this->getScalar('page'));
        if ($ready && !$errors) {
            try {
                $columns = $db->getFieldNames('admin_activity_logs');
                $required = ['id', 'actor_email', 'admin_role', 'http_method', 'route', 'response_code', 'created_at'];
                if (array_diff($required, $columns)) {
                    $ready = false;
                    $errors[] = 'Struktur tabel log belum lengkap.';
                } else {
                    $count = $db->table('admin_activity_logs');
                    $this->applyFilters($count, $filters);
                    $total = (int)$count->countAllResults();
                    $page = min($page, max(1, (int)ceil($total / self::PER_PAGE)));
                    $query = $db->table('admin_activity_logs');
                    $this->applyFilters($query, $filters);
                    $rows = $query->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')
                        ->get(self::PER_PAGE, ($page - 1) * self::PER_PAGE)->getResultArray();
                }
            } catch (\Throwable $error) {
                log_message('error', 'Admin activity log query failed: {type}', ['type' => get_class($error)]);
                $ready = false;
                $errors[] = 'Log belum dapat dimuat. Coba lagi nanti.';
            }
        }

        return view('pages/adminActivityLogs', [
            'title' => 'Log aktivitas staf', 'ready' => $ready, 'errors' => $errors,
            'logs' => $rows, 'filters' => $filters, 'page' => $page,
            'pages' => max(1, (int)ceil($total / self::PER_PAGE)), 'total' => $total,
            'queryString' => http_build_query(array_filter($filters, static fn($value) => $value !== '' && $value !== null)),
        ]);
    }

    private function applyFilters($builder, array $filters): void
    {
        $jakarta = new DateTimeZone('Asia/Jakarta');
        $utc = new DateTimeZone('UTC');
        if ($filters['from']) {
            $from = new DateTimeImmutable($filters['from'] . ' 00:00:00', $jakarta);
            $builder->where('created_at >=', $from->setTimezone($utc)->format('Y-m-d H:i:s'));
        }
        if ($filters['to']) {
            $to = new DateTimeImmutable($filters['to'] . ' 23:59:59', $jakarta);
            $builder->where('created_at <=', $to->setTimezone($utc)->format('Y-m-d H:i:s'));
        }
        if ($filters['actor'] !== '') $builder->like('actor_email', $filters['actor']);
        if ($filters['route'] !== '') $builder->like('route', $filters['route']);
        if ($filters['method'] !== '') $builder->where('http_method', $filters['method']);
    }

    private function validDate(string $value): ?string
    {
        if ($value === '') return null;
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) return null;
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $issues = DateTimeImmutable::getLastErrors();
        if (!$date || ($issues !== false && ($issues['warning_count'] || $issues['error_count'])) || $date->format('Y-m-d') !== $value) return null;
        return $value;
    }

    private function getScalar(string $key): string
    {
        $value = $this->request->getGet($key);
        return is_string($value) || is_numeric($value) ? (string)$value : '';
    }
}
