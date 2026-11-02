# xfpdf

[![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%20%7C%208.4-777BB4?logo=php)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-40%20passing-brightgreen)](#development)
[![Coverage](https://img.shields.io/badge/coverage-84%25-yellow)](#development)

### Extended FPDF including FPDF, FPDF Protection, HTML2PDF, FPDI and helpers.

The FPDF library is made up of the following elements:

- the main file, fpdf.php, which contains the class
- the font definition files located in the font directory

The font definition files are necessary as soon as you want to output some text in a document.
If they are not accessible, the SetFont() method will produce the following error:

FPDF error: Could not include font definition file


Remarks:

- Only the files corresponding to the fonts actually used are necessary
- The tutorials provided in this package are ready to be executed



## Requirements

- PHP 7.4 or 8.4+
- Composer

## Installation

```bash
composer require camilord/xfpdf
```

## Basic usage

```php
use camilord\xfpdf\XFPDF_CORE;

$pdf = new XFPDF_CORE();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(40, 10, 'Hello World!');
$pdf->Output();
```

Or extend the core class:

```php
use camilord\xfpdf\XFPDF_CORE;

class MyPDF extends XFPDF_CORE
{
    // custom header/footer or helpers
}
```

## Features

- FPDF 1.7 compatible API
- HTML to PDF (`HTML2PDF` / `XFPDF_CORE::WriteHTML`)
- PDF protection (`FPDF_Protection`)
- FPDI page import and templates
- Font utilities in `makefont/`

## Development

Install dependencies:

```bash
composer install
```

Run the test suite:

```bash
vendor/bin/phpunit
```

Run with coverage:

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit --coverage-text
```

## PHP compatibility

The codebase is tested on PHP 7.4 and PHP 8.4. Deprecated and removed PHP features have been replaced with compatible alternatives while keeping the public API intact.