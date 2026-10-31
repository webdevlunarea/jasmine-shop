<?php

use App\Models\PemesananModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class AdminOrdersVisibilityTest extends CIUnitTestCase
{
    public function testAdminListIncludesAllEnvironmentsAndKeepsStatusFiltering(): void
    {
        $db = Database::connect([
            'DBDriver' => 'SQLite3',
            'database' => ':memory:',
            'DBPrefix' => '',
            'DBDebug' => true,
        ], false);

        try {
            $db->query('CREATE TABLE pemesanan (id INTEGER PRIMARY KEY, id_midtrans TEXT, email_cus TEXT, status TEXT, data_mid TEXT)');
            $db->table('pemesanan')->insertBatch([
                ['id_midtrans' => 'PROD-1', 'email_cus' => 'buyer@example.test', 'status' => 'Proses', 'data_mid' => '{"_app_payment_environment":"production"}'],
                ['id_midtrans' => 'SANDBOX-1', 'email_cus' => 'test@example.test', 'status' => 'Proses', 'data_mid' => '{"_app_payment_environment":"sandbox"}'],
                ['id_midtrans' => 'LEGACY-1', 'email_cus' => 'buyer@example.test', 'status' => 'Selesai', 'data_mid' => '{}'],
                ['id_midtrans' => 'LEGACY-TEST-1', 'email_cus' => 'test@example.test', 'status' => 'Dikirim', 'data_mid' => '{}'],
            ]);

            $model = new PemesananModel($db);

            self::assertSame(3, $model->countAdminOrders());
            self::assertSame(1, $model->countAdminProductionOrders());
            self::assertSame(1, $model->countAdminOrders('Proses'));
            self::assertSame(['LEGACY-TEST-1', 'LEGACY-1', 'PROD-1'], array_column($model->getAdminOrdersPage(null, 20, 0), 'id_midtrans'));
        } finally {
            $db->close();
        }
    }
}
