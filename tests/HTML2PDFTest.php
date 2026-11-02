<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\HTML2PDF;
use PHPUnit\Framework\TestCase;

class HTML2PDFTest extends TestCase
{
    public function testWriteHtmlTags(): void
    {
        $im = imagecreatetruecolor(10, 10);
        $img = tempnam(sys_get_temp_dir(), 'png') . '.png';
        imagepng($im, $img);
        imagedestroy($im);

        $pdf = new HTML2PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->WriteHTML(
            '<b>bold</b> <i>italic</i> <u>under</u> <strong>s</strong> <em>e</em> '
            . '<a href="http://example.com">link</a> <br> <p>para</p> '
            . '<font color="#FF0000" face="arial">red</font> <blockquote>quote</blockquote> '
            . '<img src="' . $img . '" width="10" height="10">'
        );
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($img);
        unlink($file);
    }

    public function testPdfHtmlAlias(): void
    {
        $pdf = new HTML2PDF();
        $pdf->PDF_HTML('L', 'mm', 'A4');
        $this->assertNotNull($pdf);
    }
}
