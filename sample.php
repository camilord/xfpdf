<?php

/**
 * Created by PhpStorm.
 * User: Camilo3rd
 * Date: 19/01/2018
 * Time: 12:30 AM
 */

use camilord\xfpdf\XFPDF_CORE;

function __autoload($class_name) {
    $filename = str_replace(['//', '\\'], '/',__DIR__.'/'.basename($class_name).'.php');
    include_once($filename);
}

$pdf = new XFPDF_CORE('L', 'mm', 'A4');

$pdf->Open();
$pdf->SetMargins(20,50);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

$font_pdf = 'Arial';
$font_size = 12;
$pdf->SetFont($font_pdf,'',$font_size);
$pdf->WriteHTML('<b>Hello World!</b>');

$pdf->Output();