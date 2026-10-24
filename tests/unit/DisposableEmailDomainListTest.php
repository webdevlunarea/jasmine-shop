<?php

use App\Services\Auth\DisposableEmailDomainList;
use CodeIgniter\Test\CIUnitTestCase;

final class DisposableEmailDomainListTest extends CIUnitTestCase
{
    public function testRefreshValidatesAndAtomicallyCachesTheListForParentDomainMatching(): void
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lunarea-disposable-' . bin2hex(random_bytes(6)) . '.txt';
        $lines = ['tempbox.test', 'maildrop.test'];
        for ($index = 0; $index < 998; $index++) $lines[] = 'provider' . $index . '.test';
        $list = new DisposableEmailDomainList($path, static function (string $url) use ($lines): string {
            PHPUnit\Framework\Assert::assertSame(DisposableEmailDomainList::SOURCE_URL, $url);
            return implode("\n", $lines) . "\n";
        });

        try {
            self::assertSame(1000, $list->refresh());
            self::assertTrue($list->isListed('tempbox.test'));
            self::assertTrue($list->isListed('sub.maildrop.test'));
            self::assertFalse($list->isListed('ordinary.example'));
        } finally {
            @unlink($path);
        }
    }

    public function testRefreshKeepsPreviousListWhenUpstreamDataIsInvalid(): void
    {
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'lunarea-disposable-' . bin2hex(random_bytes(6)) . '.txt';
        $original = implode("\n", array_map(static fn(int $index): string => 'safe' . $index . '.test', range(0, 999))) . "\n";
        file_put_contents($path, $original);
        $list = new DisposableEmailDomainList($path, static fn(string $url): string => "only-one.test\n");

        try {
            try {
                $list->refresh();
                self::fail('Short upstream lists must not replace the active cache.');
            } catch (RuntimeException) {
                self::assertSame($original, file_get_contents($path));
            }
        } finally {
            @unlink($path);
        }
    }
}
