<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\pdf_context;
use PHPUnit\Framework\TestCase;

class PdfContextTest extends TestCase
{
    public function testStringMode(): void
    {
        $str = 'abc';
        $ctx = new pdf_context($str);
        $this->assertSame(0, $ctx->getPos());
        $this->assertSame(3, $ctx->length);
        $ctx->reset();
        $this->assertSame(0, $ctx->offset);
        $this->assertTrue($ctx->ensureContent());
        $this->assertFalse($ctx->increaseLength());
    }

    public function testFileMode(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'txt');
        file_put_contents($file, "hello world");
        $handle = fopen($file, 'rb');
        $ctx = new pdf_context($handle);
        $this->assertGreaterThan(0, $ctx->length);
        $ctx->reset(0, 5);
        $this->assertSame(5, $ctx->length);
        $ctx->reset(6, 5);
        $this->assertTrue($ctx->ensureContent());
        fclose($handle);
        unlink($file);
    }
}
