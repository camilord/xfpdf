<?php
/**
 * Created by PhpStorm.
 * User: Camilo3rd
 * Date: 19/01/2018
 * Time: 12:22 AM
 */

namespace camilord\xfpdf;


use camilord\xfpdf\fpdf\FPDF_Protection;

class XFPDF extends FPDF_Protection
{

    public function __construct($orientation='P', $unit='mm', $size='A4')
    {
        parent::__construct($orientation, $unit, $size);
    }

    public function getAbout() {
        return 'Enhanced by Camilo3rd';
    }
}