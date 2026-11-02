<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\XFPDF_CORE;
use PHPUnit\Framework\TestCase;

class XFPDFCoreTest extends TestCase
{
    public function testWriteHtml(): void
    {
        $im = imagecreatetruecolor(10, 10);
        $img = tempnam(sys_get_temp_dir(), 'png') . '.png';
        imagepng($im, $img);
        imagedestroy($im);

        $pdf = new XFPDF_CORE();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->WriteHTML(
            '<h1>H1</h1><h2>H2</h2><h3>H3</h3><h4>H4</h4>'
            . '<p>paragraph</p><br><red>red</red><blue>blue</blue>'
            . '<blockquote>quote</blockquote><pre>preformatted</pre>'
            . '<b>bold</b><i>italic</i><u>under</u><strong>strong</strong><em>em</em>'
            . '<a href="http://example.com">link</a>'
            . '<img src="' . $img . '" width="10" height="10">'
            . '<li>item</li><lib>lib</lib><hr>'
            . '<font color="#0000FF" face="arial">font</font>'
            . '<table border="1"><tr><td width="10" height="10" align="center" bgcolor="#FFFFFF">cell</td></tr></table>'
            . '<pagebreak><runpdf>'
        );
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($img);
        unlink($file);
    }

    public function testCreateTable(): void
    {
        $pdf = new XFPDF_CORE();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 8);
        $pdf->createTable(['A', 'B', 'C', 'D', 'E', 'F'], [['1', '2', '3', '4', '5', '6'], ['a', 'b', 'c', 'd', 'e', 'f']]);
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }

    public function testRenderParagraph(): void
    {
        $pdf = new XFPDF_CORE();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);
        $pdf->renderParagraph('short text', 10, 20, 10);
        $pdf->renderParagraph('this is a very long building address that should wrap across multiple lines', 10, 20, 10);
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }

    public function testMagicAndDefaultFont(): void
    {
        $pdf = new XFPDF_CORE();
        $pdf->setDefaultFont('Courier');
        $pdf->font_family = 'Times';
        $this->assertSame('Times', $pdf->font_family);
    }

    public function testHelpers(): void
    {
        $pdf = new XFPDF_CORE();
        $this->assertSame(['R' => 0, 'G' => 0, 'B' => 255], $pdf->hex2dec('#0000FF'));
        $this->assertEquals(10 * 25.4 / 72, $pdf->px2mm(10));
        $this->assertSame('abc...', $pdf->ellipsis('abcd', 3));
        $this->assertSame('ab', $pdf->ellipsis('ab', 3));
    }
}
