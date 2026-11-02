<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\fpdi_pdf_parser;
use PHPUnit\Framework\TestCase;

class FpdiPdfParserTest extends TestCase
{
    public function testParser(): void
    {
        $src = new \camilord\xfpdf\FPDI();
        $src->AddPage();
        $src->SetFont('Arial', 'B', 16);
        $src->Cell(40, 10, 'Source Page');
        $source = tempnam(sys_get_temp_dir(), 'src') . '.pdf';
        $src->Output($source, 'F');

        $parser = new fpdi_pdf_parser($source);
        $this->assertGreaterThan(0, $parser->getPageCount());
        $parser->setPageNo(1);
        $res = $parser->getPageResources();
        $this->assertTrue(is_array($res) || $res === false);
        $content = $parser->getContent();
        $this->assertIsString($content);
        $boxes = $parser->getPageBoxes(1, 1);
        $this->assertIsArray($boxes);
        $rot = $parser->getPageRotation(1);
        $this->assertTrue(is_array($rot) || $rot === false);
        $parser->closeFile();
        unlink($source);
    }
}
