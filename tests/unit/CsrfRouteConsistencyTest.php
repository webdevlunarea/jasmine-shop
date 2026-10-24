<?php

use CodeIgniter\Test\CIUnitTestCase;

final class CsrfRouteConsistencyTest extends CIUnitTestCase
{
    public function testRouteFilteredActionsAreNotManuallyVerifiedAgain(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $security = file_get_contents(APPPATH . 'Config/Security.php');
        $pages = file_get_contents(APPPATH . 'Controllers/Pages.php');
        $address = file_get_contents(APPPATH . 'Controllers/CustomerAddresses.php');
        $mapping = file_get_contents(APPPATH . 'Controllers/LunaMappingAdmin.php');
        $subsidy = file_get_contents(APPPATH . 'Controllers/ShippingSubsidyAdmin.php');

        foreach ([
            "'/account/addresses/manage'", "'/cancelorder/(:any)'", "'/payorder/(:any)'",
            "'/payorder/(:any)/confirm/(:any)'", "'/lunamappingadmin/save'", "'/shippingadmin/subsidy'",
        ] as $path) {
            $this->assertMatchesRegularExpression(
                '~\\$routes->post\\(' . preg_quote($path, '~') . '.*csrf~',
                $routes,
                'Expected route-level CSRF protection for ' . $path
            );
        }

        foreach ([$pages, $address, $mapping, $subsidy] as $controller) {
            $this->assertDoesNotMatchRegularExpression(
                '~(?:Services::security\\(\\)|security\\(\\))->verify\\(~',
                $controller,
                'A controller must not re-verify a token after the route CSRF filter.'
            );
        }

        $this->assertMatchesRegularExpression(
            '~public bool \\$regenerate = false;~',
            $security,
            'CSRF token should remain stable across tabs until its cookie expires.'
        );
    }
}
