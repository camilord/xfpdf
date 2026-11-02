<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\FPDI;
use PHPUnit\Framework\TestCase;

class FPDITest extends TestCase
{
    public function testImportPage(): void
    {
        $src = new FPDI();
        $src->AddPage();
        $src->SetFont('Arial', 'B', 16);
        $src->Cell(40, 10, 'Source Page');
        $source = tempnam(sys_get_temp_dir(), 'src') . '.pdf';
        $src->Output($source, 'F');

        $pdf = new FPDI();
        $pdf->AddPage();
        $count = $pdf->setSourceFile($source);
        $this->assertSame(1, $count);
        $tpl = $pdf->importPage(1);
        $this->assertIsInt($tpl);
        $pdf->useTemplate($tpl, 10, 10, 100);

        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));

        $pdf->cleanUp();
        unlink($source);
        unlink($file);
    }

    public function testVersionAndBox(): void
    {
        $pdf = new FPDI();
        $pdf->setPdfVersion('1.5');
        $this->assertSame('1.5', $pdf->getPdfVersion());
        $pdf->AddPage();
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }

    public function testImportWithOptions(): void
    {
        $src = new FPDI();
        $src->AddPage();
        $src->SetFont('Arial', 'B', 16);
        $src->Cell(40, 10, 'Source Page');
        $source = tempnam(sys_get_temp_dir(), 'src') . '.pdf';
        $src->Output($source, 'F');

        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->setSourceFile($source);
        $tpl = $pdf->importPage(1, 'MediaBox', false);
        $this->assertIsInt($tpl);
        $box = $pdf->getLastUsedPageBox();
        $this->assertNotNull($box);
        $pdf->useTemplate($tpl, null, null, 0, 0, true);
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        $pdf->cleanUp();
        unlink($source);
        unlink($file);
    }

    public function testTemplate(): void
    {
        $pdf = new FPDI();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);
        $tpl = $pdf->beginTemplate(20, 20, 50, 50);
        $pdf->Cell(20, 10, 'TPL');
        $pdf->endTemplate();
        $pdf->useTemplate($tpl, 50, 50);

        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }
}
