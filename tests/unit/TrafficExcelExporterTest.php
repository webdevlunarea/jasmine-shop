<?php

namespace Tests\Unit;

use App\Libraries\TrafficExcelExporter;
use PHPUnit\Framework\TestCase;

final class TrafficExcelExporterTest extends TestCase
{
    public function testItCreatesAReadableMultiSheetXlsxWorkbook(): void
    {
        if (!class_exists(\ZipArchive::class)) self::markTestSkipped('ZipArchive is required.');

        $bytes = (new TrafficExcelExporter())->generate([
            [
                'title' => 'Ringkasan',
                'rows' => [['LUNAREA — TRAFFIC'], ['Periode uji'], [], ['Metrik', 'Nilai'], ['Pageviews', 42]],
                'widths' => [28, 16],
                'headerRows' => [4],
                'numericColumns' => [2],
                'filterHeaderRow' => 4,
            ],
            [
                'title' => 'Kunjungan',
                'rows' => [['LUNAREA — KUNJUNGAN'], ['Periode uji'], [], ['Waktu', 'Path'], ['2026-10-22', '/']],
                'widths' => [24, 48],
                'headerRows' => [4],
                'filterHeaderRow' => 4,
            ],
        ]);

        self::assertStringStartsWith("PK\x03\x04", $bytes);
        $path = tempnam(sys_get_temp_dir(), 'traffic-xlsx-test-');
        self::assertNotFalse($path);
        try {
            file_put_contents($path, $bytes);
            $zip = new \ZipArchive();
            self::assertSame(true, $zip->open($path));
            self::assertNotFalse($zip->locateName('xl/worksheets/sheet1.xml'));
            self::assertNotFalse($zip->locateName('xl/worksheets/sheet2.xml'));
            self::assertNotFalse($zip->locateName('xl/styles.xml'));
            self::assertStringContainsString('LUNAREA — TRAFFIC', (string) $zip->getFromName('xl/worksheets/sheet1.xml'));
            self::assertStringContainsString('autoFilter', (string) $zip->getFromName('xl/worksheets/sheet1.xml'));
            $zip->close();
        } finally {
            @unlink($path);
        }
    }
}
