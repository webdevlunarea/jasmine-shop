<?php

namespace Tests\Unit;

use App\Services\Auth\AdminPermissions;
use CodeIgniter\Test\CIUnitTestCase;

final class AdminPermissionsTest extends CIUnitTestCase
{
    public function testPrivilegedRolesCanAccessEverything(): void
    {
        foreach (['DEV', 'SUPER_ADMIN'] as $role) {
            $this->assertTrue(AdminPermissions::canAccess($role, 'system.maintenance'));
            $this->assertTrue(AdminPermissions::canAccess($role, 'finance.write'));
            $this->assertTrue(AdminPermissions::canAccess($role, 'admin.manage'));
        }
        $this->assertFalse(AdminPermissions::canAccess('SUPER_ADMIN', 'admin.roles.manage'));
        $this->assertTrue(AdminPermissions::canAccess('DEV', 'admin.roles.manage'));
    }

    public function testOperationalRolesAreRestrictedByFunction(): void
    {
        $this->assertTrue(AdminPermissions::canAccess('ORDER_ADMIN', 'orders.write'));
        $this->assertFalse(AdminPermissions::canAccess('ORDER_ADMIN', 'finance.write'));
        $this->assertFalse(AdminPermissions::canAccess('ORDER_ADMIN', 'customers.manage'));
        $this->assertTrue(AdminPermissions::canAccess('CATALOG_ADMIN', 'catalog.write'));
        $this->assertFalse(AdminPermissions::canAccess('CATALOG_ADMIN', 'orders.write'));
        $this->assertTrue(AdminPermissions::canAccess('FINANCE_ADMIN', 'finance.write'));
        $this->assertFalse(AdminPermissions::canAccess('FINANCE_ADMIN', 'orders.write'));
        $this->assertFalse(AdminPermissions::canAccess('ADMIN', 'system.maintenance'));
        $this->assertFalse(AdminPermissions::canAccess('CONTENT_ADMIN', AdminPermissions::requiredPermission('/actionbroadcastvoucher/ABC', 'POST')));
        $this->assertFalse(AdminPermissions::canAccess('UNKNOWN_ROLE', 'orders.read'));
        $this->assertTrue(AdminPermissions::canAccess('CUSTOM_CONTENT_EDITOR', 'content.write', ['content.write']));
        $this->assertTrue(AdminPermissions::canAccess('CUSTOM_CONTENT_EDITOR', 'content.read', ['content.write']));
        $this->assertFalse(AdminPermissions::canAccess('CUSTOM_CONTENT_EDITOR', 'orders.read', ['content.write']));
        $this->assertFalse(AdminPermissions::canAccess('CUSTOM_CONTENT_EDITOR', 'admin.roles.manage', ['admin.roles.manage']));
    }

    public function testRoutesFailClosedWhenNotMapped(): void
    {
        $this->assertSame('catalog.read', AdminPermissions::requiredPermission('/listproduct', 'GET'));
        $this->assertSame('catalog.write', AdminPermissions::requiredPermission('/editproduct/ABC', 'GET'));
        $this->assertSame('finance.write', AdminPermissions::requiredPermission('/payorder/ABC', 'POST'));
        $this->assertSame('finance.write', AdminPermissions::requiredPermission('/actionaddinvoiceadmin', 'POST'));
        $this->assertSame('system.maintenance', AdminPermissions::requiredPermission('/benerinstokluna', 'GET'));
        $this->assertSame('admin.manage', AdminPermissions::requiredPermission('/unexpected-admin-path', 'GET'));
        $this->assertSame('admin.roles.manage', AdminPermissions::requiredPermission('/admin/roles/save', 'POST'));
        $this->assertSame('settings.audit.read', AdminPermissions::requiredPermission('/admin/activity-logs', 'GET'));
        $this->assertSame('settings.audit.read', AdminPermissions::requiredPermission('/admin/activity-logs/', 'GET'));
        $this->assertSame('orders.read', AdminPermissions::requiredPermission('/admin/returns', 'GET'));
    }

    public function testActivityLogsHaveASeparateReadPermission(): void
    {
        $this->assertTrue(AdminPermissions::canAccess('DEV', 'settings.audit.read'));
        $this->assertTrue(AdminPermissions::canAccess('ADMIN', 'settings.audit.read'));
        $this->assertFalse(AdminPermissions::canAccess('CONTENT_ADMIN', 'settings.audit.read'));
        $this->assertTrue(AdminPermissions::canAccess('CUSTOM_AUDIT_VIEWER', 'settings.audit.read', ['settings.audit.read']));
        $this->assertFalse(AdminPermissions::canAccess('CUSTOM_AUDIT_VIEWER', 'admin.roles.manage', ['settings.audit.read']));
    }

    public function testDashboardIsAvailableToRecognizedStaffRolesOnly(): void
    {
        $this->assertSame('admin.dashboard', AdminPermissions::requiredPermission('/admin/dashboard', 'GET'));
        foreach (['DEV', 'ADMIN', 'ORDER_ADMIN', 'CATALOG_ADMIN', 'CONTENT_ADMIN', 'FINANCE_ADMIN', 'CUSTOM_CONTENT_EDITOR'] as $role) {
            $this->assertTrue(AdminPermissions::canAccess($role, 'admin.dashboard'));
        }
        $this->assertFalse(AdminPermissions::canAccess('UNKNOWN_ROLE', 'admin.dashboard'));
    }
}
