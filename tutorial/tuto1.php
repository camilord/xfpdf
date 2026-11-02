<?php

require('../FPDF.php');
use camilord\xfpdf\FPDF;

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, 'Hello World!');
$pdf->Output();
