<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\FPDF_TPL;
use PHPUnit\Framework\TestCase;

class FPDFTplTest extends TestCase
{
    public function testTemplateFeatures(): void
    {
        $im = imagecreatetruecolor(10, 10);
        $img = tempnam(sys_get_temp_dir(), 'png') . '.png';
        imagepng($im, $img);
        imagedestroy($im);

        $pdf = new FPDF_TPL();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $tpl = $pdf->beginTemplate(5, 5, 50, 50);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Image($img, 5, 5, 10, 10);
        $pdf->endTemplate();
        $link = $pdf->AddLink();
        $pdf->SetLink($link, 5, 1);
        $pdf->Link(5, 5, 10, 10, 'http://example.com');
        $size = $pdf->getTemplateSize($tpl);
        $this->assertIsArray($size);
        $pdf->useTemplate($tpl, 10, 10, 20, 20);
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($img);
        unlink($file);
    }
}
