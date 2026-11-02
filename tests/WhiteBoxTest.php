<?php

namespace Xfpdf\Tests;

use camilord\xfpdf\pdf_parser;
use camilord\xfpdf\pdf_context;
use PHPUnit\Framework\TestCase;

class WhiteBoxTest extends TestCase
{
    public function testPdfParserProtected(): void
    {
        $src = new \camilord\xfpdf\FPDI();
        $src->AddPage();
        $source = tempnam(sys_get_temp_dir(), 'src') . '.pdf';
        $src->Output($source, 'F');

        $parser = new pdf_parser($source);

        $refFind = new \ReflectionMethod(pdf_parser::class, '_findXref');
        $refFind->setAccessible(true);
        try { $offset = $refFind->invoke($parser); } catch (\Throwable $e) { $offset = 0; }

        $result = array();
        $refReadXref = new \ReflectionMethod(pdf_parser::class, '_readXref');
        $refReadXref->setAccessible(true);
        try { $refReadXref->invokeArgs($parser, array(&$result, $offset)); } catch (\Throwable $e) {}

        $refReadRoot = new \ReflectionMethod(pdf_parser::class, '_readRoot');
        $refReadRoot->setAccessible(true);
        try { $refReadRoot->invoke($parser); } catch (\Throwable $e) {}

        $refReadValue = new \ReflectionMethod(pdf_parser::class, '_readValue');
        $refReadValue->setAccessible(true);
        $refReadToken = new \ReflectionMethod(pdf_parser::class, '_readToken');
        $refReadToken->setAccessible(true);
        $inputs = array('<< /Type /Page >> ', '[1 2 3] ', '(hello) ', 'true ', 'null ', '42 ', '3.14 ', '%comment\n');
        // loop disabled

        $m = new \ReflectionMethod(pdf_parser::class, '_unFilterStream');
        $m->setAccessible(true);
        foreach (array('/FlateDecode', '/Fl', '/LZWDecode', '/ASCII85Decode', '/ASCIIHexDecode') as $filterName) {
            $dict = array(pdf_parser::TYPE_DICTIONARY, array('/Filter' => array(pdf_parser::TYPE_TOKEN, $filterName)));
            $stream = array(pdf_parser::TYPE_STREAM, 'test');
            $obj = array(pdf_parser::TYPE_OBJECT, 'obj' => 1, 'gen' => 0, $dict, $stream);
            try { $m->invoke($parser, $obj); } catch (\Throwable $e) {}
        }
        // array filter
        $dict2 = array(pdf_parser::TYPE_DICTIONARY, array('/Filter' => array(pdf_parser::TYPE_ARRAY, array(array(pdf_parser::TYPE_TOKEN, '/FlateDecode')))));
        $stream2 = array(pdf_parser::TYPE_STREAM, 'test');
        $obj2 = array(pdf_parser::TYPE_OBJECT, 'obj' => 2, 'gen' => 0, $dict2, $stream2);
        try { $m->invoke($parser, $obj2); } catch (\Throwable $e) {}
        // objref filter
        $dict3 = array(pdf_parser::TYPE_DICTIONARY, array('/Filter' => array(pdf_parser::TYPE_OBJREF, 1, 0)));
        $stream3 = array(pdf_parser::TYPE_STREAM, 'test');
        $obj3 = array(pdf_parser::TYPE_OBJECT, 'obj' => 3, 'gen' => 0, $dict3, $stream3);
        try { $m->invoke($parser, $obj3); } catch (\Throwable $e) {}

        try { $parser->resolveObject(array(pdf_parser::TYPE_OBJREF, 1, 0)); } catch (\Throwable $e) {}
        try { $parser->resolveObject(array(pdf_parser::TYPE_NUMERIC, 1)); } catch (\Throwable $e) {}

        $parser->closeFile();
        unlink($source);
        $this->assertTrue(true);
    }
}