<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

final class CustomerNotifications extends BaseController
{
    private function customerEmail(): ?string
    {
        if (!session('isLogin') || (string) session('role') !== '0'
            || (string) session('active') !== '1' || !is_string(session('email'))
            || session('email') === 'tamu') {
            return null;
        }

        return trim((string) session('email'));
    }

    public function index()
    {
        $email = $this->customerEmail();
        if ($email === null) return redirect()->to('/account');

        $db = db_connect();
        $available = $db->tableExists('customer_push_notifications');
        $rows = [];
        $total = 0;
        $unread = 0;
        $page = max(1, (int) $this->request->getGet('page'));
        $pageSize = 30;

        if ($available) {
            $table = $db->table('customer_push_notifications')
                ->where('email_user', $email)->whereIn('status', ['sent', 'read', 'awaiting_device']);
            $total = (clone $table)->countAllResults();
            $unread = $db->table('customer_push_notifications')
                ->where('email_user', $email)->where('status', 'sent')->countAllResults();
            $lastPage = max(1, (int) ceil($total / $pageSize));
            $page = min($page, $lastPage);
            $rows = $db->table('customer_push_notifications')
                ->select('id, notification_type, title, body, status, created_at, sent_at')
                ->where('email_user', $email)
                ->whereIn('status', ['sent', 'read', 'awaiting_device'])
                ->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')
                ->limit($pageSize, ($page - 1) * $pageSize)->get()->getResultArray();
        }

        return view('pages/customerNotifications', [
            'title' => 'Pemberitahuan Saya',
            'notifications' => $rows,
            'available' => $available,
            'total' => $total,
            'unread' => $unread,
            'page' => $page,
            'lastPage' => max(1, (int) ceil($total / $pageSize)),
            'message' => session()->getFlashdata('customer_notification_message'),
        ]);
    }

    public function markRead(): RedirectResponse
    {
        $email = $this->customerEmail();
        if ($email === null) return redirect()->to('/account');

        $db = db_connect();
        if (!$db->tableExists('customer_push_notifications')) {
            return redirect()->to('/notifications')->with('customer_notification_message', 'Pemberitahuan belum tersedia.');
        }

        $builder = $db->table('customer_push_notifications')
            ->where('email_user', $email)->where('status', 'sent');
        if ((string) $this->request->getPost('all') === '1') {
            $builder->update(['status' => 'read', 'updated_at' => gmdate('Y-m-d H:i:s')]);
        } else {
            $rawId = $this->request->getPost('id');
            $id = filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if ($id === false) {
                return redirect()->to('/notifications')->with('customer_notification_message', 'Pemberitahuan yang dipilih tidak valid.');
            }
            $builder->where('id', (int) $id)->update([
                'status' => 'read', 'updated_at' => gmdate('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to('/notifications')->with('customer_notification_message', 'Pemberitahuan diperbarui.');
    }
}
