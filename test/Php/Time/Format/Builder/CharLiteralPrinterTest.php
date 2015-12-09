<?php

namespace Php\Time\Format\Builder;


use PHPUnit_Framework_TestCase;

class CharLiteralPrinterTest extends AbstractTestPrinterParser
{
    //-----------------------------------------------------------------------
public function test_print_emptyCalendrical()  {
buf.append("EXISTING");
getFormatter('a').formatTo(EMPTY_DTA, buf);
assertEquals(buf.toString(), "EXISTINGa");
}

public void test_print_dateTime()  {
    buf.append("EXISTING");
    getFormatter('a').formatTo(dta, buf);
    assertEquals(buf.toString(), "EXISTINGa");
}

    public void test_print_emptyAppendable() {
    getFormatter('a').formatTo(dta, buf);
    assertEquals(buf.toString(), "a");
}

    //-----------------------------------------------------------------------
    public void test_toString() {
    assertEquals(getFormatter('a').toString(), "'a'");
}

    //-----------------------------------------------------------------------
    public void test_toString_apos() {
    assertEquals(getFormatter('\'').toString(), "''");
}

}