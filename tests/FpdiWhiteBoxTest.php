<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\FPDI;
use PHPUnit\Framework\TestCase;

class FpdiWhiteBoxTest extends TestCase
{
    public function testProtected(): void
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
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl, 10, 10, 100);

        // _straightOut
        $m = new \ReflectionMethod(FPDI::class, '_straightOut');
        $m->setAccessible(true);
        $m->invoke($pdf, 'hello');

        // _writeValue
        $w = new \ReflectionMethod(FPDI::class, '_writeValue');
        $w->setAccessible(true);
        $vals = array(
            array(FPDI::class, 'x'), // placeholder not used
        );
        // Use pdf_parser constants via FPDI? Access through class
        $pc = 'camilord\\xfpdf\\pdf_parser';
        $v=array($pc::TYPE_TOKEN, 'foo'); $w->invokeArgs($pdf, array(&$v));
        $v=array($pc::TYPE_NUMERIC, 42); $w->invokeArgs($pdf, array(&$v));
        $v=array($pc::TYPE_REAL, 3.14); $w->invokeArgs($pdf, array(&$v));
        $v=array($pc::TYPE_HEX, '4142'); $w->invokeArgs($pdf, array(&$v));
        $v=array($pc::TYPE_STRING, 'str'); $w->invokeArgs($pdf, array(&$v));
        $v=array($pc::TYPE_BOOLEAN, true); $w->invokeArgs($pdf, array(&$v));
        $v=array($pc::TYPE_NULL); $w->invokeArgs($pdf, array(&$v));
        $v=array($pc::TYPE_ARRAY, array(array($pc::TYPE_NUMERIC, 1), array($pc::TYPE_NUMERIC, 2))); $w->invokeArgs($pdf, array(&$v));
        $v=array($pc::TYPE_DICTIONARY, array('/Type' => array($pc::TYPE_TOKEN, '/Page'))); $w->invokeArgs($pdf, array(&$v));
        $v=array($pc::TYPE_OBJREF, 1, 0); $w->invokeArgs($pdf, array(&$v));
        $dict = array($pc::TYPE_DICTIONARY, array('/Filter' => array($pc::TYPE_TOKEN, '/FlateDecode')));
        $stream = array($pc::TYPE_STREAM, 'data');
        $v=array($pc::TYPE_STREAM, $dict, $stream); $w->invokeArgs($pdf, array(&$v));
        $v='notarray'; $w->invokeArgs($pdf, array(&$v));

        // _putformxobjects
        $pfo = new \ReflectionMethod(FPDI::class, '_putformxobjects');
        $pfo->setAccessible(true);
        try { $pfo->invoke($pdf); } catch (\Throwable $e) {}

        // _putimportedobjects
        $pio = new \ReflectionMethod(FPDI::class, '_putimportedobjects');
        $pio->setAccessible(true);
        try { $pio->invoke($pdf); } catch (\Throwable $e) {}

        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        $pdf->cleanUp();
        unlink($source);
        unlink($file);
    }
}