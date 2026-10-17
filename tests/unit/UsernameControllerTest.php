<?php

use App\Controllers\CustomerUsername;
use App\Services\Auth\CustomerUsernames;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class UsernameControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private $connection;

    protected function setUp(): void
    {
        parent::setUp(); session()->destroy();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE user (email TEXT PRIMARY KEY, role TEXT, active TEXT)');
        $this->connection->query('CREATE TABLE customer_usernames (username TEXT PRIMARY KEY, email_user TEXT UNIQUE)');
        $this->connection->table('user')->insert(['email' => 'buyer@example.test', 'role' => '0', 'active' => '1']);
    }
    protected function tearDown(): void { session()->destroy(); $this->connection->close(); parent::tearDown(); }

    private function controller(array $post, string $method = 'POST'): CustomerUsername
    {
        return new class(new CustomerUsernames($this->connection), $post, $method) extends CustomerUsername {
            private CustomerUsernames $store;
            public function __construct(CustomerUsernames $store, array $post, string $method) {
                $this->store=$store;
                $this->request=new class($post,$method) {
                    private array $post; private string $method;
                    public function __construct(array $post,string $method) { $this->post=$post; $this->method=$method; }
                    public function getMethod() { return $this->method; }
                    public function getPost($key) { return $this->post[$key]??null; }
                    public function getVar($key) { throw new \RuntimeException('POST only'); }
                };
                $this->response=\Config\Services::response(null,false);
            }
            protected function usernames(): CustomerUsernames { return $this->store; }
        };
    }

    private function signedIn(): void { session()->set(['isLogin'=>true,'email'=>'buyer@example.test','role'=>'0','active'=>'1']); }

    public function testSaveAlwaysUsesSessionOwnerAndPreservesCommerceSession(): void
    {
        $this->signedIn(); session()->set('keranjang', [['id'=>123]]);
        $response=$this->controller(['username'=>'Pembeli_01','email'=>'victim@example.test','email_user'=>'victim@example.test'])->save();
        $this->assertStringEndsWith('/account#account-username',$response->getHeaderLine('Location'));
        $this->assertSame('pembeli_01',(new CustomerUsernames($this->connection))->current('buyer@example.test'));
        $this->assertNull((new CustomerUsernames($this->connection))->current('victim@example.test'));
        $this->assertSame([['id'=>123]],session('keranjang'));
        $this->assertNotEmpty(session()->getFlashdata('username_message'));
    }

    public function testInvalidNameReturnsFeedbackWithoutSaving(): void
    {
        $this->signedIn();
        $this->controller(['username'=>['invalid']])->save();
        $this->assertNotEmpty(session()->getFlashdata('username_error'));
        $this->assertSame(0,$this->connection->table('customer_usernames')->countAllResults());
    }

    public function testGetAndGuestCannotSave(): void
    {
        $this->assertSame(403,$this->controller(['username'=>'pembeli_01'])->save()->getStatusCode());
        $this->signedIn();
        $this->assertSame(403,$this->controller(['username'=>'pembeli_01'],'GET')->save()->getStatusCode());
        $this->assertSame(0,$this->connection->table('customer_usernames')->countAllResults());
    }

    public function testUsernameRouteRequiresCsrfForSignedInCustomer(): void
    {
        $this->expectException(\CodeIgniter\Security\Exceptions\SecurityException::class);
        $this->withSession(['isLogin'=>true,'email'=>'buyer@example.test','role'=>'0','active'=>'1'])
            ->post('/account/username',['username'=>'pembeli_01']);
    }

    public function testProfilePasswordChangesAlsoRequireCsrf(): void
    {
        $this->expectException(\CodeIgniter\Security\Exceptions\SecurityException::class);
        $this->withSession(['isLogin'=>true,'email'=>'buyer@example.test','role'=>'0','active'=>'1'])
            ->post('/account',['sandi'=>'UnwantedPassword123']);
    }
}
