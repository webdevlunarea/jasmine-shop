<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;

abstract class ApiController extends BaseController
{
    protected function success(array $data, array $meta = [], int $status = 200)
    {
        return $this->response->setStatusCode($status)->setJSON(['success' => true, 'data' => $data, 'meta' => (object)$meta]);
    }

    protected function failure(string $code, string $message, int $status, array $fields = [])
    {
        $error = ['code' => $code, 'message' => $message];
        if ($fields) $error['fields'] = $fields;
        return $this->response->setStatusCode($status)->setJSON(['success' => false, 'error' => $error]);
    }

    protected function payload(): array
    {
        $data = $this->request->getJSON(true);
        return is_array($data) ? $data : $this->request->getPost();
    }

    protected function authEmail(): string
    {
        return strtolower(trim($this->request->getHeaderLine('X-Lunarea-Authenticated-Customer')));
    }
}
