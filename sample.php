<?php

/**
 * Created by PhpStorm.
 * User: Camilo3rd
 * Date: 19/01/2018
 * Time: 12:30 AM
 */

spl_autoload_register(function ($class) {
    $class = ltrim($class, '\\');
    $prefix = 'camilord\\xfpdf\\';
    if (strpos($class, $prefix) === 0) {
        $relative = substr($class, strlen($prefix));
        $file = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }

    $base = basename(str_replace('\\', '/', $class)) . '.php';
    foreach (array(__DIR__ . '/' . $base, __DIR__ . '/filters/' . $base) as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

use camilord\xfpdf\XFPDF_CORE;

$pdf = new XFPDF_CORE('L', 'mm', 'A4');

$pdf->Open();
$pdf->SetMargins(20, 50);
$pdf->SetAutoPageBreak(true, 25);
$pdf->AddPage();

$font_pdf = 'Arial';
$font_size = 12;
$pdf->SetFont($font_pdf, '', $font_size);
$pdf->WriteHTML('<b>Hello World!</b>');

$pdf->Output();
