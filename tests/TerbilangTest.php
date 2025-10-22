<?php
// SPDX-License-Identifier: MIT

use PHPUnit\Framework\TestCase;
use Nggit\PHPTerbilang\Terbilang;

class TerbilangTest extends TestCase
{
    protected $t;

    protected function setUp(): void
    {
        echo "\n[" . $this->name() . "]\n";

        $this->t = new Terbilang();
    }

    public function testBasic()
    {
        $words = [
            'nol', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh',
            'delapan', 'sembilan', 'sepuluh', 'sebelas', 'dua belas'
        ];

        foreach ($words as $num => $word) {
            $this->t->parse($num);
            $this->assertEquals($word, $this->t->getResult(), "Failed at number: $num");
        }

        $this->t->parse('1100');
        $this->assertEquals('seribu seratus', $this->t->getResult());

        $this->t->parse('200001');
        $this->assertEquals('dua ratus ribu satu', $this->t->getResult());

        $this->t->parse('1001');
        $this->assertEquals('seribu satu', $this->t->getResult());

        $this->t->parse('21001');
        $this->assertEquals('dua puluh satu ribu satu', $this->t->getResult());
    }

    public function testLargeNumbers()
    {
        $this->t->parse('1000000000');
        $this->assertEquals('satu miliar', $this->t->getResult());

        $this->t->parse('11000000000000000');
        $this->assertEquals('sebelas ribu triliun', $this->t->getResult());

        $this->t->parse('19000000000000000000071000102011000210');
        $this->assertEquals(
            'sembilan belas undesiliun, tujuh puluh satu ribu triliun, seratus dua miliar sebelas juta dua ratus sepuluh',
            $this->t->getResult()
        );
    }

    public function testNegativeNumbers()
    {
        $this->t->parse('-1,0');
        $this->assertEquals('minus satu koma nol', $this->t->getResult());
    }

    public function testSeparator()
    {
        $this->t->parse('1.000,00');
        $this->assertEquals('seribu koma nol nol', $this->t->getResult());

        $t = new Terbilang('1000.00', '.');
        $this->assertEquals('seribu koma nol nol', $t->getResult());
    }

    public function testSeparatorAutodetect()
    {
        $this->t->parse('0,1');
        $this->assertEquals('nol koma satu', $this->t->getResult());

        $this->t->parse('0.1');
        $this->assertEquals('nol koma satu', $this->t->getResult());

        $this->t->parse('1,000');
        $this->assertEquals('satu koma nol nol nol', $this->t->getResult());

        $this->t->parse('1.00');
        $this->assertEquals('satu koma nol nol', $this->t->getResult());

        $this->t->parse('1.000');
        $this->assertEquals('seribu', $this->t->getResult());
    }

    public function testInvalidSeparator()
    {
        $this->expectException('Exception');
        $this->t->parse('1000', ';');
    }

    public function testTooLarge()
    {
        $this->expectException('Exception');
        $this->t->parse(str_repeat('9', 109));
    }

    public function testEmpty()
    {
        $this->t->parse('');
        $this->assertEquals('', $this->t->getResult());
    }
}
