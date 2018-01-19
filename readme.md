xfpdf
=====

I created this package so that I don't need to include to my project. 

Credits to FPDF developers.

Usage:

Add this to your project's composer.json...

```
{
    "require": {
        "camilord/xfpdf": "dev-master"
    }
}
```

Usage:

```

$pdf = new XFPDF_CORE();

```

```
use camilord/xfpdf/XFPDF_CORE;

class myPDF extends XFPDF_CORE {
   
}

```
