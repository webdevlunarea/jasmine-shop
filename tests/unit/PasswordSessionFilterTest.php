<?php

use App\Filters\PasswordSessionFilter;
use App\Services\Auth\PasswordSessionVersions;
use CodeIgniter\Test\CIUnitTestCase;

final class PasswordSessionFilterTest extends CIUnitTestCase
{
    private $connection;
    protected function setUp(): void {
        parent::setUp(); session()->destroy();
        $this->connection=\Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true],false);
        $this->connection->query('CREATE TABLE customer_password_sessions (email_user TEXT PRIMARY KEY, version TEXT)');
    }
    protected function tearDown(): void { session()->destroy(); $this->connection->close(); parent::tearDown(); }
    private function filter(): PasswordSessionFilter {
        return new class(new PasswordSessionVersions($this->connection)) extends PasswordSessionFilter {
            private PasswordSessionVersions $store;
            public function __construct(PasswordSessionVersions $store) { $this->store=$store; }
            protected function versions(): PasswordSessionVersions { return $this->store; }
        };
    }
    public function testLegacySessionRemainsValidUntilPasswordReset(): void {
        session()->set(['isLogin'=>true,'email'=>'buyer@example.test','role'=>'0']);
        $this->assertNull($this->filter()->before(\Config\Services::request()));
        $this->assertTrue(session('isLogin'));
    }
    public function testResetVersionRevokesOldSessionBeforeControllerRuns(): void {
        session()->set(['isLogin'=>true,'email'=>'buyer@example.test','role'=>'0','password_session_version'=>'old']);
        $this->connection->table('customer_password_sessions')->insert(['email_user'=>'buyer@example.test','version'=>'new']);
        $response=$this->filter()->before(\Config\Services::request());
        $this->assertStringEndsWith('/login',$response->getHeaderLine('Location'));
        $this->assertFalse((bool)session('isLogin'));
    }
    public function testFreshVersionAndAnonymousRequestsPass(): void {
        $this->assertNull($this->filter()->before(\Config\Services::request()));
        $this->connection->table('customer_password_sessions')->insert(['email_user'=>'buyer@example.test','version'=>'new']);
        session()->set(['isLogin'=>true,'email'=>'buyer@example.test','password_session_version'=>'new']);
        $this->assertNull($this->filter()->before(\Config\Services::request()));
    }
}
