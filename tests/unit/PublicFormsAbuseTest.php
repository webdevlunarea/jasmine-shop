<?php

use App\Controllers\Pages;
use CodeIgniter\Test\CIUnitTestCase;

final class PublicFormsAbuseTest extends CIUnitTestCase
{
    private function controller(string $ip, array $post): Pages
    {
        return new class($ip, $post) extends Pages {
            public function __construct(string $ip, array $post)
            {
                $this->request = new class($ip, $post) {
                    public function __construct(private string $ip, private array $post) {}
                    public function getIPAddress(): string { return $this->ip; }
                    public function getPost(string $key) { return $this->post[$key] ?? null; }
                };
                $this->response = \Config\Services::response(null, false);
            }
        };
    }

    public function testContactHoneypotDoesNotReachDatabaseOrMailer(): void
    {
        $controller = $this->controller('192.0.2.' . random_int(1, 250), ['website' => 'spam.example']);
        $response = $controller->actionForm();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/formthanks', $response->getHeaderLine('Location'));
    }

    public function testContactFormIsThrottledByIpBeforeDatabaseOrMailer(): void
    {
        // Invalid payloads stop at validation; the sixth attempt must stop at the IP limiter first.
        $controller = $this->controller('198.51.100.' . random_int(1, 250), []);
        for ($attempt = 0; $attempt < 5; ++$attempt) $controller->actionForm();
        $response = $controller->actionForm();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/form', $response->getHeaderLine('Location'));
        $this->assertSame('Pesan terlalu sering dikirim. Silakan coba lagi beberapa saat.', session()->getFlashdata('val-msg'));
    }
}
