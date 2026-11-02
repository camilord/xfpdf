<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\FPDF_Protection;
use PHPUnit\Framework\TestCase;

class ProtectionTest extends TestCase
{
    public function testProtectedPdf(): void
    {
        $pdf = new FPDF_Protection();
        $pdf->SetProtection(['copy', 'print'], 'user', 'owner');
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, 'Protected');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $content = file_get_contents($file);
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('/Encrypt', $content);
        unlink($file);
    }
}
