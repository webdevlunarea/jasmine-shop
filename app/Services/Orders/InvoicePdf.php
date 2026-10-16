<?php

namespace App\Services\Orders;

use Dompdf\Dompdf;
use Dompdf\Options;

final class InvoicePdf
{
    public function render(array $invoice): string
    {
        $options = new Options();
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);
        $options->setIsJavascriptEnabled(false);
        $options->setChroot([ROOTPATH . 'vendor/dompdf', FCPATH . 'img']);
        $options->setTempDir(WRITEPATH . 'cache');
        $options->setFontCache(WRITEPATH . 'cache');
        $options->setDefaultFont('DejaVu Sans');
        $pdf = new Dompdf($options);
        $pdf->setPaper('A4', 'portrait');
        $pdf->loadHtml(view('pages/invoice', ['invoice'=>$invoice, 'pdf'=>true], ['saveData'=>false]), 'UTF-8');
        $pdf->render();
        $canvas = $pdf->getCanvas();
        $font = $pdf->getFontMetrics()->getFont('DejaVu Sans');
        $canvas->page_text(42, 810, 'Lunarea Furniture', $font, 8, [0.4,0.4,0.4]);
        $canvas->page_text(450, 810, 'Halaman {PAGE_NUM} / {PAGE_COUNT}', $font, 8, [0.4,0.4,0.4]);
        return $pdf->output();
    }
}
