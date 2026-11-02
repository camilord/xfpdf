<?php

define('FPDF_FONTPATH', '.');
require('../FPDF.php');
use camilord\xfpdf\FPDF;

$pdf = new FPDF();
$pdf->AddFont('Calligrapher', '', 'calligra.php');
$pdf->AddPage();
$pdf->SetFont('Calligrapher', '', 35);
$pdf->Cell(0, 10, 'Enjoy new fonts with FPDF!');
$pdf->Output();
