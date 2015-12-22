<?php

namespace Php\Time\Format\Builder;

class CharLiteralPrinterTest extends AbstractTestPrinterParser
{
    //-----------------------------------------------------------------------
    public function test_print_emptyCalendrical()
    {
        $buf = "EXISTING";
        $this->getFormatterChar('a')->formatTo(self::EMPTY_DTA(), $buf);
        $this->assertEquals($buf, "EXISTINGa");
    }

    public function test_print_dateTime()
    {
        $buf = "EXISTING";
        $this->getFormatterChar('a')->formatTo($this->dta, $buf);
        $this->assertEquals($buf, "EXISTINGa");
    }

    public
    function test_print_emptyAppendable()
    {
        $buf = '';
        $this->getFormatterChar('a')->formatTo($this->dta, $buf);
        $this->assertEquals($buf, "a");
    }

    //-----------------------------------------------------------------------
    public function test_toString()
    {
        $this->assertEquals($this->getFormatterChar('a')->__toString(), "'a'");
    }

    //-----------------------------------------------------------------------
    public function test_toString_apos()
    {
        $this->assertEquals($this->getFormatterChar('\'')->__toString(), "''");
    }

}