<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\FPDF;
use PHPUnit\Framework\TestCase;

class FPDFTest extends TestCase
{
    public function testBasicPdfGeneration(): void
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(40, 10, 'Hello World!');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');

        $this->assertFileExists($file);
        $content = file_get_contents($file);
        $this->assertStringStartsWith('%PDF-', $content);
        unlink($file);
    }

    public function testWriteAndMultiCell(): void
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Write(5, "Hello\nWorld");
        $pdf->Ln();
        $pdf->MultiCell(0, 5, "Line one\nLine two");
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }

    public function testImagePng(): void
    {
        $im = imagecreatetruecolor(10, 10);
        imagefill($im, 0, 0, imagecolorallocate($im, 255, 0, 0));
        $img = tempnam(sys_get_temp_dir(), 'png') . '.png';
        imagepng($im, $img);
        imagedestroy($im);

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->Image($img, 10, 10, 20, 20);
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($img);
        unlink($file);
    }

    public function testComprehensiveDocument(): void
    {
        $im = imagecreatetruecolor(10, 10);
        imagefill($im, 0, 0, imagecolorallocate($im, 0, 255, 0));
        $png = tempnam(sys_get_temp_dir(), 'png') . '.png';
        imagepng($im, $png);
        $jpg = tempnam(sys_get_temp_dir(), 'jpg') . '.jpg';
        imagejpeg($im, $jpg);
        $gif = tempnam(sys_get_temp_dir(), 'gif') . '.gif';
        imagegif($im, $gif);
        imagedestroy($im);

        $pdf = new FPDF('L', 'mm', 'A4');
        $pdf->SetTitle('Title', true);
        $pdf->SetSubject('Subject');
        $pdf->SetAuthor('Author');
        $pdf->SetKeywords('keywords');
        $pdf->SetCreator('Creator');
        $pdf->AliasNbPages();
        $pdf->SetDisplayMode('fullpage', 'two');
        $pdf->SetCompression(true);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage('P', 'A5');
        $pdf->SetDrawColor(255, 0, 0);
        $pdf->SetFillColor(0, 255, 0);
        $pdf->SetTextColor(0, 0, 255);
        $pdf->SetLineWidth(0.5);
        $pdf->Line(10, 10, 50, 10);
        $pdf->Rect(10, 12, 20, 10, 'DF');
        $pdf->SetFont('Arial', 'BUI', 12);
        $pdf->SetFontSize(14);
        $link = $pdf->AddLink();
        $pdf->SetLink($link, 5, 1);
        $pdf->Cell(40, 10, 'Cell', 1, 1, 'C', true, 'http://example.com');
        $pdf->MultiCell(60, 5, "Multi\nCell", 'LTRB', 'J', true);
        $pdf->Write(5, "Write text ", $link);
        $pdf->Ln(5);
        $pdf->Text(10, 40, 'Text');
        $pdf->Image($png, 10, 45, 20, 20);
        $pdf->Image($jpg, 35, 45, 20, 20, 'JPG');
        $pdf->Image($gif, 60, 45, 20, 20);
        $pdf->Link(10, 45, 20, 20, 'http://example.com');
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->SetX($x + 5);
        $pdf->SetY($y + 5);
        $pdf->SetXY(10, 70);
        $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo());
        $pdf->AddPage();
        $pdf->SetFont('Times', '', 10);
        $pdf->Cell(0, 10, 'Second page with {nb} alias');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $content = file_get_contents($file);
        $this->assertStringStartsWith('%PDF-', $content);
        $this->assertStringContainsString('/Page', $content);

        unlink($png);
        unlink($jpg);
        unlink($gif);
        unlink($file);
    }

    public function testAddFontCore(): void
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->AddFont('Helvetica', 'B');
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(40, 10, 'Helvetica bold');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }

    public function testEmbeddedFont(): void
    {
        $dir = tempnam(sys_get_temp_dir(), 'fontdir');
        unlink($dir);
        mkdir($dir);
        if (!function_exists('MakeFont')) {
            define('MAKEFONT_SKIP_CLI', true);
            require_once '/srv/workspace/xfpdf/makefont/makefont.php';
        }
        $old = getcwd();
        chdir($dir);
        MakeFont('/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', 'cp1252', true);
        chdir($old);

        $pdf = new FPDF();
        $pdf->fontpath = $dir . '/';
        $pdf->AddPage();
        $pdf->AddFont('DejaVuSans', '', 'DejaVuSans.php');
        $pdf->SetFont('DejaVuSans', '', 12);
        $pdf->Cell(40, 10, 'Embedded font');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        array_map('unlink', glob($dir . '/*'));
        rmdir($dir);
        unlink($file);
    }

    public function testAlphaPng(): void
    {
        $im = imagecreatetruecolor(10, 10);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);
        $img = tempnam(sys_get_temp_dir(), 'png') . '.png';
        imagepng($im, $img);
        imagedestroy($im);

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->Image($img, 10, 10, 20, 20);
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($img);
        unlink($file);
    }

    public function testGrayscaleAlphaPng(): void
    {
        $im = imagecreate(10, 10);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $transparent);
        $img = tempnam(sys_get_temp_dir(), 'png') . '.png';
        imagepng($im, $img);
        imagedestroy($im);

        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->Image($img, 10, 10, 20, 20);
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($img);
        unlink($file);
    }

    public function testMultiCellBranches(): void
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(30, 5, "A very long line that should wrap automatically across multiple lines", 'LTRB', 'J', true);
        $pdf->MultiCell(30, 5, "Line1\nLine2\nLine3", 1, 'C');
        $pdf->MultiCell(0, 5, '');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }

    public function testWriteLong(): void
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 10);
        $pdf->Write(5, str_repeat('word ', 50));
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }

    public function testOutputString(): void
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, 'Hello');
        $s = $pdf->Output('', 'S');
        $this->assertStringStartsWith('%PDF-', $s);
    }

    public function testUnitsAndDisplayModes(): void
    {
        foreach (array('pt', 'mm', 'cm', 'in') as $unit) {
            $pdf = new FPDF('P', $unit, 'Letter');
            $pdf->SetDisplayMode(150);
            $pdf->SetDisplayMode('fullwidth');
            $pdf->SetDisplayMode('real');
            $pdf->SetDisplayMode('fullpage');
            $pdf->AddPage();
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(40, 10, $unit);
            $file = tempnam(sys_get_temp_dir(), 'pdf');
            $pdf->Output($file, 'F');
            $this->assertStringStartsWith('%PDF-', file_get_contents($file));
            unlink($file);
        }
    }

    public function testSetTitleUtf8(): void
    {
        $pdf = new FPDF();
        $pdf->SetTitle('Título', true);
        $pdf->SetSubject('Sübject', true);
        $pdf->SetAuthor('Aüthor', true);
        $pdf->SetKeywords('key', true);
        $pdf->SetCreator('Creatör', true);
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, 'UTF8');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }

    public function testSetFontCore(): void
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('arial', 'IB', 12);
        $pdf->SetFont('', 'U', 10);
        $pdf->Cell(40, 10, 'font');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($file);
    }

    public function testType1Font(): void
    {
        $dir = tempnam(sys_get_temp_dir(), 'typedir');
        unlink($dir);
        mkdir($dir);
        copy('/usr/share/fonts/X11/Type1/NimbusSans-Bold.pfb', $dir . '/NimbusSans-Bold.pfb');
        copy('/usr/share/fonts/type1/urw-base35/NimbusSans-Bold.afm', $dir . '/NimbusSans-Bold.afm');

        if (!function_exists('MakeFont')) {
            define('MAKEFONT_SKIP_CLI', true);
            require_once '/srv/workspace/xfpdf/makefont/makefont.php';
        }
        $old = getcwd();
        chdir($dir);
        MakeFont($dir . '/NimbusSans-Bold.pfb', 'cp1252', true);
        chdir($old);

        $pdf = new FPDF();
        $pdf->fontpath = $dir . '/';
        $pdf->AddPage();
        $pdf->AddFont('NimbusSans', 'B', 'NimbusSans-Bold.php');
        $pdf->SetFont('NimbusSans', 'B', 12);
        $pdf->Cell(40, 10, 'Type1 font');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        array_map('unlink', glob($dir . '/*'));
        rmdir($dir);
        unlink($file);
    }

    public function testFontEncodingDiff(): void
    {
        $dir = tempnam(sys_get_temp_dir(), 'encdir');
        unlink($dir);
        mkdir($dir);

        if (!function_exists('MakeFont')) {
            define('MAKEFONT_SKIP_CLI', true);
            require_once '/srv/workspace/xfpdf/makefont/makefont.php';
        }
        $old = getcwd();
        chdir($dir);
        MakeFont('/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', 'cp1250', true);
        chdir($old);

        $pdf = new FPDF();
        $pdf->fontpath = $dir . '/';
        $pdf->AddPage();
        $pdf->AddFont('DejaVuSans2', '', 'DejaVuSans.php');
        $pdf->SetFont('DejaVuSans2', '', 12);
        $pdf->Cell(40, 10, 'Encoded font');
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        array_map('unlink', glob($dir . '/*'));
        rmdir($dir);
        unlink($file);
    }

    public function testMoreBranches(): void
    {
        // 3-byte UTF-8 title
        $pdf = new FPDF();
        $pdf->SetTitle("中文", true);
        // landscape init
        $pdf2 = new FPDF('L', 'cm', 'A4');
        $pdf2->AddPage('L', array(200, 300));
        // display modes
        $pdf2->SetDisplayMode(120, 'single');
        $pdf2->SetDisplayMode('fullwidth', 'continuous');
        $pdf2->SetDisplayMode('real', 'two');
        // Cell border string and fill false
        $pdf2->SetFont('Arial', '', 12);
        $pdf2->Cell(40, 10, 'Cell', 'LTRB', 1, 'C', false);
        // MultiCell J with many spaces
        $pdf2->MultiCell(60, 5, "word " . str_repeat("word ", 20), 'LTRB', 'J');
        // Write long word without space
        $pdf2->Write(5, str_repeat('a', 200));
        $pdf2->SetX(50);
        $pdf2->Write(5, str_repeat('b', 200));
        // Rect
        $pdf2->Rect(10, 10, 20, 10, 'D');
        $pdf2->Rect(10, 25, 20, 10, 'F');
        // Indexed (palette) PNG
        $im = imagecreate(10, 10);
        imagecolorallocate($im, 255, 0, 0);
        $img = tempnam(sys_get_temp_dir(), 'png') . '.png';
        imagepng($im, $img);
        imagedestroy($im);
        $pdf2->Image($img, 10, 40, 20, 20);
        $file = tempnam(sys_get_temp_dir(), 'pdf');
        $pdf2->Output($file, 'F');
        $this->assertStringStartsWith('%PDF-', file_get_contents($file));
        unlink($img);
        unlink($file);
    }

    public function testOutputDestinations(): void
    {
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, 'Hello');

        // S
        $s = $pdf->Output('doc.pdf', 'S');
        $this->assertStringStartsWith('%PDF-', $s);

        // I with whitespace-only output buffer
        ob_start();
        echo "   \n";
        ob_start();
        $pdf->Output('doc.pdf', 'I');
        $out = ob_get_clean();
        ob_end_clean();
        $this->assertStringStartsWith('%PDF-', $out);
    }
}
