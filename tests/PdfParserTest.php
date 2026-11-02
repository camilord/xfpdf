<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\pdf_parser;
use PHPUnit\Framework\TestCase;

class PdfParserTest extends TestCase
{
    public function testParser(): void
    {
        $src = new \camilord\xfpdf\FPDI();
        $src->AddPage();
        $src->SetFont('Arial', 'B', 16);
        $src->Cell(40, 10, 'Source Page');
        $source = tempnam(sys_get_temp_dir(), 'src') . '.pdf';
        $src->Output($source, 'F');

        $parser = new pdf_parser($source);
        $this->assertMatchesRegularExpression('/^\d\.\d$/', $parser->getPdfVersion());
        $parser->getEncryption();
        $obj = $parser->resolveObject(array(pdf_parser::TYPE_OBJREF, 1, 0));
        $this->assertTrue(is_array($obj) || $obj === false);
        $parser->closeFile();
        unlink($source);
    }
}
