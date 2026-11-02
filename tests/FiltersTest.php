<?php

namespace Xfpdf\Tests;

use PHPUnit\Framework\TestCase;

class FiltersTest extends TestCase
{
    public function testAsciiHexDecode(): void
    {
        if (!class_exists('FilterASCIIHexDecode')) {
            require __DIR__ . '/../filters/FilterASCIIHexDecode.php';
        }
        $filter = new \FilterASCIIHexDecode();
        $this->assertSame('abc', $filter->decode('616263>'));
        $this->assertSame('616263>', $filter->encode('abc'));
    }

    public function testAscii85Decode(): void
    {
        if (!class_exists('FilterASCII85')) {
            require __DIR__ . '/../filters/FilterASCII85.php';
        }
        $filter = new \FilterASCII85();
        $this->assertSame('Man ', $filter->decode('9jqo^'));
        $this->assertSame("\x00\x00\x00\x00", $filter->decode('z'));
        $this->expectException(\LogicException::class);
        $filter->encode('abc');
    }

    public function testLzwDecode(): void
    {
        if (!class_exists('FilterLZW')) {
            require __DIR__ . '/../filters/FilterLZW.php';
        }
        $filter = new \FilterLZW();
        $this->assertSame("\x00", $filter->decode("\x00\x00"));
        $this->expectException(\LogicException::class);
        $filter->encode('abc');
    }
}
