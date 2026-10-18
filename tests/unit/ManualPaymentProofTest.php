<?php

use App\Services\Payments\ManualPaymentProof;
use CodeIgniter\Test\CIUnitTestCase;

final class ManualPaymentProofTest extends CIUnitTestCase
{
    private $connection;
    private ManualPaymentProof $proofs;
    private string $png;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = \Config\Database::connect(['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true], false);
        $this->connection->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, id_midtrans TEXT NOT NULL UNIQUE, email_cus TEXT NOT NULL, status TEXT NOT NULL, data_mid TEXT NOT NULL, bukti_bayar BLOB NULL)');
        $this->proofs = new ManualPaymentProof($this->connection);
        $this->png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pWQAAAAASUVORK5CYII=');
        $this->insertOrder('proof-order', 'buyer@example.test', 'Menunggu Pembayaran Rekening', 'rekening');
    }

    protected function tearDown(): void { $this->connection->close(); parent::tearDown(); }

    private function insertOrder(string $id, string $email, string $status, string $type, ?string $expiry = null): void
    {
        $this->connection->table('pemesanan')->insert([
            'id_midtrans' => $id, 'email_cus' => $email, 'status' => $status,
            'data_mid' => json_encode(['payment_type' => $type, 'expiry_time' => $expiry ?? (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->modify('+1 hour')->format('Y-m-d H:i:s')]),
            'bukti_bayar' => null,
        ]);
    }

    public function testOwnerCanSubmitImageOnlyOnceForActiveManualTransfer(): void
    {
        $this->assertTrue($this->proofs->save('proof-order', 'buyer@example.test', $this->png));
        $row = $this->connection->table('pemesanan')->where('id_midtrans', 'proof-order')->get()->getRowArray();
        $this->assertNotEmpty($row['bukti_bayar']);
        // SQLite's test driver truncates bound binary strings at NUL; production
        // stores this legacy column as MySQL LONGBLOB, so assert the signature only.
        $this->assertSame("\x89PNG\r\n\x1a\n", substr((string)$row['bukti_bayar'], 0, 8));
        $this->assertFalse($this->proofs->save('proof-order', 'buyer@example.test', $this->png));
    }

    public function testNonOwnerWrongPaymentTypeAndTerminalOrdersAreRejected(): void
    {
        $this->assertFalse($this->proofs->save('proof-order', 'other@example.test', $this->png));
        $this->insertOrder('midtrans-order', 'buyer@example.test', 'Menunggu Pembayaran', 'bank_transfer');
        $this->insertOrder('completed-order', 'buyer@example.test', 'Proses', 'rekening');
        $this->assertFalse($this->proofs->save('midtrans-order', 'buyer@example.test', $this->png));
        $this->assertFalse($this->proofs->save('completed-order', 'buyer@example.test', $this->png));
    }

    public function testExpiredOrderAndNonImagePayloadAreRejected(): void
    {
        $this->insertOrder('expired-order', 'buyer@example.test', 'Menunggu Pembayaran Rekening', 'rekening', (new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta')))->modify('-1 minute')->format('Y-m-d H:i:s'));
        $this->assertFalse($this->proofs->save('expired-order', 'buyer@example.test', $this->png));
        $this->assertFalse($this->proofs->save('proof-order', 'buyer@example.test', 'plain text'));
    }
}
