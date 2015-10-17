<?php
/*
 * Copyright (c) 2012, 2015, Oracle and/or its affiliates. All rights reserved.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This code is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License version 2 only, as
 * published by the Free Software Foundation.  Oracle designates this
 * particular file as subject to the "Classpath" exception as provided
 * by Oracle in the LICENSE file that accompanied this code.
 *
 * This code is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
 * version 2 for more details (a copy is included in the LICENSE file that
 * accompanied this code).
 *
 * You should have received a copy of the GNU General Public License version
 * 2 along with this work; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * Please contact Oracle, 500 Oracle Parkway, Redwood Shores, CA 94065 USA
 * or visit www.oracle.com if you need additional information or have any
 * questions.
 */

/*
 * This file is available under and governed by the GNU General Public
 * License version 2 only, as published by the Free Software Foundation.
 * However, the following notice accompanied the original version of this
 * file:
 *
 * Copyright (c) 2008-2012, Stephen Colebourne & Michael Nascimento Santos
 *
 * All rights hg qreserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *  * Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 *  * Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 *  * Neither the name of JSR-310 nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
namespace Php\Time\Format;

use Php\Time\Chrono\ChronoLocalDate;
use Php\Time\Chrono\Chronology;
use Php\Time\Format\Builder\CharLiteralPrinterParser;
use Php\Time\Format\Builder\ChronoPrinterParser;
use Php\Time\Format\Builder\CompositePrinterParser;
use Php\Time\Format\Builder\DefaultValueParser;
use Php\Time\Format\Builder\FractionPrinterParser;
use Php\Time\Format\Builder\InstantPrinterParser;
use Php\Time\Format\Builder\LocalizedOffsetIdPrinterParser;
use Php\Time\Format\Builder\LocalizedPrinterParser;
use Php\Time\Format\Builder\NumberPrinterParser;
use Php\Time\Format\Builder\OffsetIdPrinterParser;
use Php\Time\Format\Builder\PadPrinterParserDecorator;
use Php\Time\Format\Builder\ReducedPrinterParser;
use Php\Time\Format\Builder\StringLiteralPrinterParser;
use Php\Time\Format\Builder\TextPrinterParser;
use Php\Time\Format\Builder\WeekBasedFieldPrinterParser;
use Php\Time\Format\Builder\ZoneIdPrinterParser;
use Php\Time\Format\Builder\ZoneTextPrinterParser;
use Php\Time\IllegalArgumentException;
use Php\Time\Temporal\ChronoField;
use Php\Time\Temporal\TemporalField;
use Php\Time\Temporal\TemporalQueries;
use Php\Time\ZoneId;

/**
 * Builder to create date-time formatters.
 * <p>
 * This allows a {@code DateTimeFormatter} to be created.
 * All date-time formatters are created ultimately using this builder.
 * <p>
 * The basic elements of date-time can all be added:
 * <ul>
 * <li>Value - a numeric value</li>
 * <li>Fraction - a fractional value including the decimal place. Always use this when
 * outputting fractions to ensure that the fraction is parsed correctly</li>
 * <li>Text - the textual equivalent for the value</li>
 * <li>OffsetId/Offset - the {@linkplain ZoneOffset zone offset}</li>
 * <li>ZoneId - the {@linkplain ZoneId time-zone} id</li>
 * <li>ZoneText - the name of the time-zone</li>
 * <li>ChronologyId - the {@linkplain Chronology chronology} id</li>
 * <li>ChronologyText - the name of the chronology</li>
 * <li>Literal - a text literal</li>
 * <li>Nested and Optional - formats can be nested or made optional</li>
 * </ul>
 * In addition, any of the elements may be decorated by padding, either with spaces or any other character.
 * <p>
 * Finally, a shorthand pattern, mostly compatible with {@code java.text.SimpleDateFormat SimpleDateFormat}
 * can be used, see {@link #appendPattern(String)}.
 * In practice, this simply parses the pattern and calls other methods on the builder.
 *
 * @implSpec
 * This class is a mutable builder intended for use from a single thread.
 *
 * @since 1.8
 */
final class DateTimeFormatterBuilder
{

    /**
     * Query for a time-zone that is region-only.
     */
    /*private static final TemporalQuery<ZoneId> QUERY_REGION_ONLY = (temporal) -> {
    ZoneId zone = temporal.query(TemporalQueries.zoneId());
    return (zone != null && zone instanceof ZoneOffset == false ? zone : null);
    };*/

    /**
     * The currently active builder, used by the outermost builder.
     * @var DateTimeFormatterBuilder
     */
    private $active;
    /**
     * The parent builder, null for the outermost builder.
     * @var DateTimeFormatterBuilder
     */
    private $parent;
    /**
     * The list of printers that will be used.
     * @var DateTimePrinterParser[]
     */
    private $printerParsers;
    /**
     * Whether this builder produces an optional formatter.
     * @var bool
     */
    private $optional;
    /**
     * The width to pad the next field to.
     * @var int
     */
    private $padNextWidth;
    /**
     * The character to pad the next field with.
     * @var string
     */
    private $padNextChar;
    /**
     * The index of the last variable width value parser.
     * @var int
     */
    private $valueParserIndex = -1;

    /**
     * Gets the formatting pattern for date and time styles for a locale and chronology.
     * The locale and chronology are used to lookup the locale specific format
     * for the requested dateStyle and/or timeStyle.
     *
     * @param $dateStyle FormatStyle the FormatStyle for the date, null for time-only pattern
     * @param $timeStyle FormatStyle the FormatStyle for the time, null for date-only pattern
     * @param $chrono Chronology the Chronology, non-null
     * @param $locale Locale the locale, non-null
     * @return string the locale and Chronology specific formatting pattern
     * @throws IllegalArgumentException if both dateStyle and timeStyle are null
     */
    public static function getLocalizedDateTimePattern(FormatStyle $dateStyle, FormatStyle $timeStyle,
                                                       Chronology $chrono, Locale $locale)
    {
        if ($dateStyle == null && $timeStyle == null) {
            throw new IllegalArgumentException("Either dateStyle or timeStyle must be non-null");
        }
        $lr = LocaleProviderAdapter::getResourceBundleBased()->getLocaleResources($locale);
        $pattern = $lr->getJavaTimeDateTimePattern(
            self::convertStyle($timeStyle), self::convertStyle($dateStyle), $chrono->getCalendarType());
        return $pattern;
    }

    /**
     * Converts the given FormatStyle to the java.text.DateFormat style.
     *
     * @param $style FormatStyle the FormatStyle style
     * @return int the int style, or -1 if style is null, indicating un-required
     */
    private static function convertStyle(FormatStyle $style)
    {
        if ($style === null) {
            return -1;
        }

        return $style->ordinal();  // indices happen to align
    }

    /**
     * Constructs a new instance of the builder.
     */
    public function __construct()
    {
        $this->parent = null;
        $this->optional = false;
    }

    /**
     * Constructs a new instance of the builder.
     *
     * @param $parent DateTimeFormatterBuilder the parent builder, not null
     * @param $optional bool whether the formatter is optional, not null
     */
    private function __construct2(DateTimeFormatterBuilder $parent, $optional)
    {
        $this->parent = $parent;
        $this->optional = $optional;
    }

//-----------------------------------------------------------------------
    /**
     * Changes the parse style to be case sensitive for the remainder of the formatter.
     * <p>
     * Parsing can be case sensitive or insensitive - by default it is case sensitive.
     * This method allows the case sensitivity setting of parsing to be changed.
     * <p>
     * Calling this method changes the state of the builder such that all
     * subsequent builder method calls will parse text in case sensitive mode.
     * See {@link #parseCaseInsensitive} for the opposite setting.
     * The parse case sensitive/insensitive methods may be called at any point
     * in the builder, thus the parser can swap between case parsing modes
     * multiple times during the parse.
     * <p>
     * Since the default is case sensitive, this method should only be used after
     * a previous call to {@code #parseCaseInsensitive}.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public function
    parseCaseSensitive()
    {
        $this->appendInternal(SettingsParser::SENSITIVE());
        return $this;
    }

    /**
     * Changes the parse style to be case insensitive for the remainder of the formatter.
     * <p>
     * Parsing can be case sensitive or insensitive - by default it is case sensitive.
     * This method allows the case sensitivity setting of parsing to be changed.
     * <p>
     * Calling this method changes the state of the builder such that all
     * subsequent builder method calls will parse text in case insensitive mode.
     * See {@link #parseCaseSensitive()} for the opposite setting.
     * The parse case sensitive/insensitive methods may be called at any point
     * in the builder, thus the parser can swap between case parsing modes
     * multiple times during the parse.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function parseCaseInsensitive()
    {
        $this->appendInternal(SettingsParser::INSENSITIVE());
        return $this;
    }

//-----------------------------------------------------------------------
    /**
     * Changes the parse style to be strict for the remainder of the formatter.
     * <p>
     * Parsing can be strict or lenient - by default its strict.
     * This controls the degree of flexibility in matching the text and sign styles.
     * <p>
     * When used, this method changes the parsing to be strict from this point onwards.
     * As strict is the default, this is normally only needed after calling {@link #parseLenient()}.
     * The change will remain in force until the end of the formatter that is eventually
     * constructed or until {@code parseLenient} is called.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function parseStrict()
    {
        $this->appendInternal(SettingsParser::STRICT());
        return $this;
    }

    /**
     * Changes the parse style to be lenient for the remainder of the formatter.
     * Note that case sensitivity is set separately to this method.
     * <p>
     * Parsing can be strict or lenient - by default its strict.
     * This controls the degree of flexibility in matching the text and sign styles.
     * Applications calling this method should typically also call {@link #parseCaseInsensitive()}.
     * <p>
     * When used, this method changes the parsing to be lenient from this point onwards.
     * The change will remain in force until the end of the formatter that is eventually
     * constructed or until {@code parseStrict} is called.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function parseLenient()
    {
        $this->appendInternal(SettingsParser::LENIENT());
        return $this;
    }

//-----------------------------------------------------------------------
    /**
     * Appends a default value for a field to the formatter for use in parsing.
     * <p>
     * This appends an instruction to the builder to inject a default value
     * into the parsed result. This is especially useful in conjunction with
     * optional parts of the formatter.
     * <p>
     * For example, consider a formatter that parses the year, followed by
     * an optional month, with a further optional day-of-month. Using such a
     * formatter would require the calling code to check whether a full date,
     * year-month or just a year had been parsed. This method can be used to
     * default the month and day-of-month to a sensible value, such as the
     * first of the month, allowing the calling code to always get a date.
     * <p>
     * During formatting, this method has no effect.
     * <p>
     * During parsing, the current state of the parse is inspected.
     * If the specified field has no associated value, because it has not been
     * parsed successfully at that point, then the specified value is injected
     * into the parse result. Injection is immediate, thus the field-value pair
     * will be visible to any subsequent elements in the formatter.
     * As such, this method is normally called at the end of the builder.
     *
     * @param $field TemporalField the field to default the value of, not null
     * @param $value int the value to default the field to
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function parseDefaulting(TemporalField $field, $value)
    {
        $this->appendInternal(new DefaultValueParser($field, $value));
        return $this;
    }

//-----------------------------------------------------------------------
    /**
     * Appends the value of a date-time field to the formatter using a normal
     * output style.
     * <p>
     * The value of the field will be output during a format.
     * If the value cannot be obtained then an exception will be thrown.
     * <p>
     * The value will be printed as per the normal format of an integer value.
     * Only negative numbers will be signed. No padding will be added.
     * <p>
     * The parser for a variable width value such as this normally behaves greedily,
     * requiring one digit, but accepting as many digits as possible.
     * This behavior can be affected by 'adjacent value parsing'.
     * See {@link #appendValue(java.time.temporal.TemporalField, int)} for full details.
     *
     * @param $field TemporalField the field to append, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendValue(TemporalField $field)
    {
        $this->appendValue(new NumberPrinterParser($field, 1, 19, SignStyle::NORMAL()));
        return $this;
    }

    /**
     * Appends the value of a date-time field to the formatter using a fixed
     * width, zero-padded approach.
     * <p>
     * The value of the field will be output during a format.
     * If the value cannot be obtained then an exception will be thrown.
     * <p>
     * The value will be zero-padded on the left. If the size of the value
     * means that it cannot be printed within the width then an exception is thrown.
     * If the value of the field is negative then an exception is thrown during formatting.
     * <p>
     * This method supports a special technique of parsing known as 'adjacent value parsing'.
     * This technique solves the problem where a value, variable or fixed width, is followed by one or more
     * fixed length values. The standard parser is greedy, and thus it would normally
     * steal the digits that are needed by the fixed width value parsers that follow the
     * variable width one.
     * <p>
     * No action is required to initiate 'adjacent value parsing'.
     * When a call to {@code appendValue} is made, the builder
     * enters adjacent value parsing setup mode. If the immediately subsequent method
     * call or calls on the same builder are for a fixed width value, then the parser will reserve
     * space so that the fixed width values can be parsed.
     * <p>
     * For example, consider {@code builder.appendValue(YEAR).appendValue(MONTH_OF_YEAR, 2);}
     * The year is a variable width parse of between 1 and 19 digits.
     * The month is a fixed width parse of 2 digits.
     * Because these were appended to the same builder immediately after one another,
     * the year parser will reserve two digits for the month to parse.
     * Thus, the text '201106' will correctly parse to a year of 2011 and a month of 6.
     * Without adjacent value parsing, the year would greedily parse all six digits and leave
     * nothing for the month.
     * <p>
     * Adjacent value parsing applies to each set of fixed width not-negative values in the parser
     * that immediately follow any kind of value, variable or fixed width.
     * Calling any other append method will end the setup of adjacent value parsing.
     * Thus, in the unlikely event that you need to avoid adjacent value parsing behavior,
     * simply add the {@code appendValue} to another {@code DateTimeFormatterBuilder}
     * and add that to this builder.
     * <p>
     * If adjacent parsing is active, then parsing must match exactly the specified
     * number of digits in both strict and lenient modes.
     * In addition, no positive or negative sign is permitted.
     *
     * @param $field TemporalField the field to append, not null
     * @param $width int the width of the printed field, from 1 to 19
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if the width is invalid
     */
    public
    function appendValue2(TemporalField $field, $width)
    {
        if ($width < 1 || $width > 19) {
            throw new IllegalArgumentException("The width must be from 1 to 19 inclusive but was " . $width);
        }

        $pp = new NumberPrinterParser($field, $width, $width, SignStyle::NOT_NEGATIVE());
        $this->appendValue($pp);
        return $this;
    }

    /**
     * Appends the value of a date-time field to the formatter providing full
     * control over formatting.
     * <p>
     * The value of the field will be output during a format.
     * If the value cannot be obtained then an exception will be thrown.
     * <p>
     * This method provides full control of the numeric formatting, including
     * zero-padding and the positive/negative sign.
     * <p>
     * The parser for a variable width value such as this normally behaves greedily,
     * accepting as many digits as possible.
     * This behavior can be affected by 'adjacent value parsing'.
     * See {@link #appendValue(java.time.temporal.TemporalField, int)} for full details.
     * <p>
     * In strict parsing mode, the minimum number of parsed digits is {@code minWidth}
     * and the maximum is {@code maxWidth}.
     * In lenient parsing mode, the minimum number of parsed digits is one
     * and the maximum is 19 (except as limited by adjacent value parsing).
     * <p>
     * If this method is invoked with equal minimum and maximum widths and a sign style of
     * {@code NOT_NEGATIVE} then it delegates to {@code appendValue(TemporalField,int)}.
     * In this scenario, the formatting and parsing behavior described there occur.
     *
     * @param $field TemporalField the field to append, not null
     * @param $minWidth int the minimum field width of the printed field, from 1 to 19
     * @param $maxWidth int the maximum field width of the printed field, from 1 to 19
     * @param $signStyle SignStyle the positive/negative output style, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if the widths are invalid
     */
    public function appendValue3(
        TemporalField $field, $minWidth, $maxWidth, SignStyle $signStyle)
    {
        if ($minWidth === $maxWidth && $signStyle === SignStyle::NOT_NEGATIVE()) {
            return $this->appendValue($field, $maxWidth);
        }

        if ($minWidth < 1 || $minWidth > 19) {
            throw new IllegalArgumentException("The minimum width must be from 1 to 19 inclusive but was " . $minWidth);
        }
        if ($maxWidth < 1 || $maxWidth > 19) {
            throw new IllegalArgumentException("The maximum width must be from 1 to 19 inclusive but was " . $maxWidth);
        }
        if ($maxWidth < $minWidth) {
            throw new IllegalArgumentException("The maximum width must exceed or equal the minimum width but " .
                $maxWidth . " < " . $minWidth);
        }
        $pp = new NumberPrinterParser($field, $minWidth, $maxWidth, $signStyle);
        $this->appendValue($pp);
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Appends the reduced value of a date-time field to the formatter.
     * <p>
     * Since fields such as year vary by chronology, it is recommended to use the
     * {@link #appendValueReduced(TemporalField, int, int, ChronoLocalDate)} date}
     * variant of this method in most cases. This variant is suitable for
     * simple fields or working with only the ISO chronology.
     * <p>
     * For formatting, the {@code width} and {@code maxWidth} are used to
     * determine the number of characters to format.
     * If they are equal then the format is fixed width.
     * If the value of the field is within the range of the {@code baseValue} using
     * {@code width} characters then the reduced value is formatted otherwise the value is
     * truncated to fit {@code maxWidth}.
     * The rightmost characters are output to match the width, left padding with zero.
     * <p>
     * For strict parsing, the number of characters allowed by {@code width} to {@code maxWidth} are parsed.
     * For lenient parsing, the number of characters must be at least 1 and less than 10.
     * If the number of digits parsed is equal to {@code width} and the value is positive,
     * the value of the field is computed to be the first number greater than
     * or equal to the {@code baseValue} with the same least significant characters,
     * otherwise the value parsed is the field value.
     * This allows a reduced value to be entered for values in range of the baseValue
     * and width and absolute values can be entered for values outside the range.
     * <p>
     * For example, a base value of {@code 1980} and a width of {@code 2} will have
     * valid values from {@code 1980} to {@code 2079}.
     * During parsing, the text {@code "12"} will result in the value {@code 2012} as that
     * is the value within the range where the last two characters are "12".
     * By contrast, parsing the text {@code "1915"} will result in the value {@code 1915}.
     *
     * @param $field TemporalField the field to append, not null
     * @param $width int the field width of the printed and parsed field, from 1 to 10
     * @param $maxWidth int the maximum field width of the printed field, from 1 to 10
     * @param $baseValue int the base value of the range of valid values
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if the width or base value is invalid
     */
    public function appendValueReduced(TemporalField $field,
                                       $width, $maxWidth, $baseValue)
    {
        $pp = new ReducedPrinterParser($field, $width, $maxWidth, $baseValue, null);
        $this->appendValue($pp);
        return $this;
    }

    /**
     * Appends the reduced value of a date-time field to the formatter.
     * <p>
     * This is typically used for formatting and parsing a two digit year.
     * <p>
     * The base date is used to calculate the full value during parsing.
     * For example, if the base date is 1950-01-01 then parsed values for
     * a two digit year parse will be in the range 1950-01-01 to 2049-12-31.
     * Only the year would be extracted from the date, thus a base date of
     * 1950-08-25 would also parse to the range 1950-01-01 to 2049-12-31.
     * This behavior is necessary to support fields such as week-based-year
     * or other calendar systems where the parsed value does not align with
     * standard ISO years.
     * <p>
     * The exact behavior is as follows. Parse the full set of fields and
     * determine the effective chronology using the last chronology if
     * it appears more than once. Then convert the base date to the
     * effective chronology. Then extract the specified field from the
     * chronology-specific base date and use it to determine the
     * {@code baseValue} used below.
     * <p>
     * For formatting, the {@code width} and {@code maxWidth} are used to
     * determine the number of characters to format.
     * If they are equal then the format is fixed width.
     * If the value of the field is within the range of the {@code baseValue} using
     * {@code width} characters then the reduced value is formatted otherwise the value is
     * truncated to fit {@code maxWidth}.
     * The rightmost characters are output to match the width, left padding with zero.
     * <p>
     * For strict parsing, the number of characters allowed by {@code width} to {@code maxWidth} are parsed.
     * For lenient parsing, the number of characters must be at least 1 and less than 10.
     * If the number of digits parsed is equal to {@code width} and the value is positive,
     * the value of the field is computed to be the first number greater than
     * or equal to the {@code baseValue} with the same least significant characters,
     * otherwise the value parsed is the field value.
     * This allows a reduced value to be entered for values in range of the baseValue
     * and width and absolute values can be entered for values outside the range.
     * <p>
     * For example, a base value of {@code 1980} and a width of {@code 2} will have
     * valid values from {@code 1980} to {@code 2079}.
     * During parsing, the text {@code "12"} will result in the value {@code 2012} as that
     * is the value within the range where the last two characters are "12".
     * By contrast, parsing the text {@code "1915"} will result in the value {@code 1915}.
     *
     * @param $field TemporalField the field to append, not null
     * @param $width int the field width of the printed and parsed field, from 1 to 10
     * @param $maxWidth int the maximum field width of the printed field, from 1 to 10
     * @param $baseDate ChronoLocalDate the base date used to calculate the base value for the range
     *  of valid values in the parsed chronology, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if the width or base value is invalid
     */
    public
    function appendValueReduced2(
        TemporalField $field, $width, $maxWidth, ChronoLocalDate $baseDate)
    {
        $pp = new ReducedPrinterParser($field, $width, $maxWidth, 0, $baseDate);
        $this->appendValue($pp);
        return $this;
    }

    /**
     * Appends a fixed or variable width printer-parser handling adjacent value mode.
     * If a PrinterParser is not active then the new PrinterParser becomes
     * the active PrinterParser.
     * Otherwise, the active PrinterParser is modified depending on the new PrinterParser.
     * If the new PrinterParser is fixed width and has sign style {@code NOT_NEGATIVE}
     * then its width is added to the active PP and
     * the new PrinterParser is forced to be fixed width.
     * If the new PrinterParser is variable width, the active PrinterParser is changed
     * to be fixed width and the new PrinterParser becomes the active PP.
     *
     * @param $pp NumberPrinterParser the printer-parser, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    private function appendValue4(NumberPrinterParser $pp)
    {
        if ($this->active->valueParserIndex >= 0) {
            $activeValueParser = $this->active->valueParserIndex;

            // adjacent parsing mode, update setting in previous parsers
            $basePP = $this->active->printerParsers->get($activeValueParser);
            if ($pp->minWidth == $pp->maxWidth && $pp->signStyle == SignStyle::NOT_NEGATIVE()) {
                // Append the width to the subsequentWidth of the active parser
                $basePP = $basePP->withSubsequentWidth($pp->maxWidth);
                // Append the new parser as a fixed width
                $this->appendInternal($pp->withFixedWidth());
                // Retain the previous active parser
                $this->active->valueParserIndex = $activeValueParser;
            } else {
                // Modify the active parser to be fixed width
                $basePP = $basePP->withFixedWidth();
                // The new parser becomes the mew active parser
                $this->active->valueParserIndex = $this->appendInternal($pp);
            }
// Replace the modified parser with the updated one
            $this->active->printerParsers->set($activeValueParser, $basePP);
        } else {
            // The new Parser becomes the active parser
            $this->active->valueParserIndex = $this->appendInternal($pp);
        }
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Appends the fractional value of a date-time field to the formatter.
     * <p>
     * The fractional value of the field will be output including the
     * preceding decimal point. The preceding value is not output.
     * For example, the second-of-minute value of 15 would be output as {@code .25}.
     * <p>
     * The width of the printed fraction can be controlled. Setting the
     * minimum width to zero will cause no output to be generated.
     * The printed fraction will have the minimum width necessary between
     * the minimum and maximum widths - trailing zeroes are omitted.
     * No rounding occurs due to the maximum width - digits are simply dropped.
     * <p>
     * When parsing in strict mode, the number of parsed digits must be between
     * the minimum and maximum width. When parsing in lenient mode, the minimum
     * width is considered to be zero and the maximum is nine.
     * <p>
     * If the value cannot be obtained then an exception will be thrown.
     * If the value is negative an exception will be thrown.
     * If the field does not have a fixed set of valid values then an
     * exception will be thrown.
     * If the field value in the date-time to be printed is invalid it
     * cannot be printed and an exception will be thrown.
     *
     * @param $field TemporalField the field to append, not null
     * @param $minWidth int the minimum width of the field excluding the decimal point, from 0 to 9
     * @param $maxWidth int the maximum width of the field excluding the decimal point, from 1 to 9
     * @param $decimalPoint bool whether to output the localized decimal point symbol
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if the field has a variable set of valid values or
     *  either width is invalid
     */
    public function appendFraction(
        TemporalField $field, $minWidth, $maxWidth, $decimalPoint)
    {
        $this->appendInternal(new FractionPrinterParser($field, $minWidth, $maxWidth, $decimalPoint));
        return $this;
    }

//-----------------------------------------------------------------------
    /**
     * Appends the text of a date-time field to the formatter using the full
     * text style.
     * <p>
     * The text of the field will be output during a format.
     * The value must be within the valid range of the field.
     * If the value cannot be obtained then an exception will be thrown.
     * If the field has no textual representation, then the numeric value will be used.
     * <p>
     * The value will be printed as per the normal format of an integer value.
     * Only negative numbers will be signed. No padding will be added.
     *
     * @param $field TemporalField the field to append, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendText(TemporalField $field)
    {
        return $this->appendText($field, TextStyle::FULL());
    }

    /**
     * Appends the text of a date-time field to the formatter.
     * <p>
     * The text of the field will be output during a format.
     * The value must be within the valid range of the field.
     * If the value cannot be obtained then an exception will be thrown.
     * If the field has no textual representation, then the numeric value will be used.
     * <p>
     * The value will be printed as per the normal format of an integer value.
     * Only negative numbers will be signed. No padding will be added.
     *
     * @param $field TemporalField the field to append, not null
     * @param $textStyle TextStyle the text style to use, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendText2(TemporalField $field, TextStyle $textStyle)
    {
        $this->appendInternal(new TextPrinterParser($field, $textStyle, DateTimeTextProvider::getInstance()));
        return $this;
    }

    /**
     * Appends the text of a date-time field to the formatter using the specified
     * map to supply the text.
     * <p>
     * The standard text outputting methods use the localized text in the JDK.
     * This method allows that text to be specified directly.
     * The supplied map is not validated by the builder to ensure that formatting or
     * parsing is possible, thus an invalid map may throw an error during later use.
     * <p>
     * Supplying the map of text provides considerable flexibility in formatting and parsing.
     * For example, a legacy application might require or supply the months of the
     * year as "JNY", "FBY", "MCH" etc. These do not match the standard set of text
     * for localized month names. Using this method, a map can be created which
     * defines the connection between each value and the text:
     * <pre>
     * Map&lt;Long, String&gt; map = new HashMap&lt;&gt;();
     * map.put(1L, "JNY");
     * map.put(2L, "FBY");
     * map.put(3L, "MCH");
     * ...
     * builder.appendText(MONTH_OF_YEAR, map);
     * </pre>
     * <p>
     * Other uses might be to output the value with a suffix, such as "1st", "2nd", "3rd",
     * or as Roman numerals "I", "II", "III", "IV".
     * <p>
     * During formatting, the value is obtained and checked that it is in the valid range.
     * If text is not available for the value then it is output as a number.
     * During parsing, the parser will match against the map of text and numeric values.
     *
     * @param $field TemporalField the field to append, not null
     * @param $textLookup string[] the map from the value to the text
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public function appendText3(TemporalField $field, $textLookup)
    {
        /*
         * TODO
         * Map < Long, String > copy = new LinkedHashMap <> (textLookup);
        Map < TextStyle, Map < Long, String >> map = Collections->singletonMap(TextStyle::FULL(), copy);
        final LocaleStore store = new LocaleStore(map);
        $provider = new DateTimeTextProvider() {
        @Override
            public String getText(TemporalField field, long value, TextStyle style, Locale locale) {
            return store->getText(value, style);
        }

@Override
            public Iterator < Entry<String, Long >> getTextIterator(TemporalField field, TextStyle style, Locale locale) {
            return store->getTextIterator(style);
        }
        };*/
        $this->appendInternal(new TextPrinterParser($field, TextStyle::FULL(), $provider));
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Appends an instant using ISO-8601 to the formatter, formatting fractional
     * digits in groups of three.
     * <p>
     * Instants have a fixed output format.
     * They are converted to a date-time with a zone-offset of UTC and formatted
     * using the standard ISO-8601 format.
     * With this method, formatting nano-of-second outputs zero, three, six
     * or nine digits digits as necessary.
     * The localized decimal style is not used.
     * <p>
     * The instant is obtained using {@link ChronoField#INSTANT_SECONDS INSTANT_SECONDS}
     * and optionally (@code NANO_OF_SECOND). The value of {@code INSTANT_SECONDS}
     * may be outside the maximum range of {@code LocalDateTime}.
     * <p>
     * The {@linkplain ResolverStyle resolver style} has no effect on instant parsing.
     * The end-of-day time of '24:00' is handled as midnight at the start of the following day.
     * The leap-second time of '23:59:59' is handled to some degree, see
     * {@link DateTimeFormatter#parsedLeapSecond()} for full details.
     * <p>
     * An alternative to this method is to format/parse the instant as a single
     * epoch-seconds value. That is achieved using {@code appendValue(INSTANT_SECONDS)}.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public function appendInstant()
    {
        $this->appendInternal(new InstantPrinterParser(-2));
        return $this;
    }

    /**
     * Appends an instant using ISO-8601 to the formatter with control over
     * the number of fractional digits.
     * <p>
     * Instants have a fixed output format, although this method provides some
     * control over the fractional digits. They are converted to a date-time
     * with a zone-offset of UTC and printed using the standard ISO-8601 format.
     * The localized decimal style is not used.
     * <p>
     * The {@code fractionalDigits} parameter allows the output of the fractional
     * second to be controlled. Specifying zero will cause no fractional digits
     * to be output. From 1 to 9 will output an increasing number of digits, using
     * zero right-padding if necessary. The special value -1 is used to output as
     * many digits as necessary to avoid any trailing zeroes.
     * <p>
     * When parsing in strict mode, the number of parsed digits must match the
     * fractional digits. When parsing in lenient mode, any number of fractional
     * digits from zero to nine are accepted.
     * <p>
     * The instant is obtained using {@link ChronoField#INSTANT_SECONDS INSTANT_SECONDS}
     * and optionally (@code NANO_OF_SECOND). The value of {@code INSTANT_SECONDS}
     * may be outside the maximum range of {@code LocalDateTime}.
     * <p>
     * The {@linkplain ResolverStyle resolver style} has no effect on instant parsing.
     * The end-of-day time of '24:00' is handled as midnight at the start of the following day.
     * The leap-second time of '23:59:60' is handled to some degree, see
     * {@link DateTimeFormatter#parsedLeapSecond()} for full details.
     * <p>
     * An alternative to this method is to format/parse the instant as a single
     * epoch-seconds value. That is achieved using {@code appendValue(INSTANT_SECONDS)}.
     *
     * @param $fractionalDigits int the number of fractional second digits to format with,
     *  from 0 to 9, or -1 to use as many digits as necessary
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException
     */
    public function appendInstant4($fractionalDigits)
    {
        if ($fractionalDigits < -1 || $fractionalDigits > 9) {
            throw new IllegalArgumentException("The fractional digits must be from -1 to 9 inclusive but was " . $fractionalDigits);
        }

        $this->appendInternal(new InstantPrinterParser($fractionalDigits));
        return $this;
    }

//-----------------------------------------------------------------------
    /**
     * Appends the zone offset, such as '+01:00', to the formatter.
     * <p>
     * This appends an instruction to format/parse the offset ID to the builder.
     * This is equivalent to calling {@code appendOffset("+HH:MM:ss", "Z")}.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendOffsetId()
    {
        $this->appendInternal(OffsetIdPrinterParser::INSTANCE_ID_Z());
        return $this;
    }

    /**
     * Appends the zone offset, such as '+01:00', to the formatter.
     * <p>
     * This appends an instruction to format/parse the offset ID to the builder.
     * <p>
     * During formatting, the offset is obtained using a mechanism equivalent
     * to querying the temporal with {@link TemporalQueries#offset()}.
     * It will be printed using the format defined below.
     * If the offset cannot be obtained then an exception is thrown unless the
     * section of the formatter is optional.
     * <p>
     * During parsing, the offset is parsed using the format defined below.
     * If the offset cannot be parsed then an exception is thrown unless the
     * section of the formatter is optional.
     * <p>
     * The format of the offset is controlled by a pattern which must be one
     * of the following:
     * <ul>
     * <li>{@code +HH} - hour only, ignoring minute and second
     * <li>{@code +HHmm} - hour, with minute if non-zero, ignoring second, no colon
     * <li>{@code +HH:mm} - hour, with minute if non-zero, ignoring second, with colon
     * <li>{@code +HHMM} - hour and minute, ignoring second, no colon
     * <li>{@code +HH:MM} - hour and minute, ignoring second, with colon
     * <li>{@code +HHMMss} - hour and minute, with second if non-zero, no colon
     * <li>{@code +HH:MM:ss} - hour and minute, with second if non-zero, with colon
     * <li>{@code +HHMMSS} - hour, minute and second, no colon
     * <li>{@code +HH:MM:SS} - hour, minute and second, with colon
     * </ul>
     * The "no offset" text controls what text is printed when the total amount of
     * the offset fields to be output is zero.
     * Example values would be 'Z', '+00:00', 'UTC' or 'GMT'.
     * Three formats are accepted for parsing UTC - the "no offset" text, and the
     * plus and minus versions of zero defined by the pattern.
     *
     * @param $pattern string the pattern to use, not null
     * @param $noOffsetText string the text to use when the offset is zero, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendOffset($pattern, $noOffsetText)
    {
        $this->appendInternal(new OffsetIdPrinterParser($pattern, $noOffsetText));
        return $this;
    }

    /**
     * Appends the localized zone offset, such as 'GMT+01:00', to the formatter.
     * <p>
     * This appends a localized zone offset to the builder, the format of the
     * localized offset is controlled by the specified {@link FormatStyle style}
     * to this method:
     * <ul>
     * <li>{@link TextStyle#FULL full} - formats with localized offset text, such
     * as 'GMT, 2-digit hour and minute field, optional second field if non-zero,
     * and colon.
     * <li>{@link TextStyle#SHORT short} - formats with localized offset text,
     * such as 'GMT, hour without leading zero, optional 2-digit minute and
     * second if non-zero, and colon.
     * </ul>
     * <p>
     * During formatting, the offset is obtained using a mechanism equivalent
     * to querying the temporal with {@link TemporalQueries#offset()}.
     * If the offset cannot be obtained then an exception is thrown unless the
     * section of the formatter is optional.
     * <p>
     * During parsing, the offset is parsed using the format defined above.
     * If the offset cannot be parsed then an exception is thrown unless the
     * section of the formatter is optional.
     * <p>
     * @param $style TextStyle the format style to use, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if style is neither {@link TextStyle#FULL
     * full} nor {@link TextStyle#SHORT short}
     */
    public
    function appendLocalizedOffset(TextStyle $style)
    {
        if ($style !== TextStyle::FULL() && $style != TextStyle::SHORT()) {
            throw new IllegalArgumentException("Style must be either full or short");
        }
        $this->appendInternal(new LocalizedOffsetIdPrinterParser($style));
        return $this;
    }

//-----------------------------------------------------------------------
    /**
     * Appends the time-zone ID, such as 'Europe/Paris' or '+02:00', to the formatter.
     * <p>
     * This appends an instruction to format/parse the zone ID to the builder.
     * The zone ID is obtained in a strict manner suitable for {@code ZonedDateTime}.
     * By contrast, {@code OffsetDateTime} does not have a zone ID suitable
     * for use with this method, see {@link #appendZoneOrOffsetId()}.
     * <p>
     * During formatting, the zone is obtained using a mechanism equivalent
     * to querying the temporal with {@link TemporalQueries#zoneId()}.
     * It will be printed using the result of {@link ZoneId#getId()}.
     * If the zone cannot be obtained then an exception is thrown unless the
     * section of the formatter is optional.
     * <p>
     * During parsing, the text must match a known zone or offset.
     * There are two types of zone ID, offset-based, such as '+01:30' and
     * region-based, such as 'Europe/London'. These are parsed differently.
     * If the parse starts with '+', '-', 'UT', 'UTC' or 'GMT', then the parser
     * expects an offset-based zone and will not match region-based zones.
     * The offset ID, such as '+02:30', may be at the start of the parse,
     * or prefixed by  'UT', 'UTC' or 'GMT'. The offset ID parsing is
     * equivalent to using {@link #appendOffset(String, String)} using the
     * arguments 'HH:MM:ss' and the no offset string '0'.
     * If the parse starts with 'UT', 'UTC' or 'GMT', and the parser cannot
     * match a following offset ID, then {@link ZoneOffset#UTC} is selected.
     * In all other cases, the list of known region-based zones is used to
     * find the longest available match. If no match is found, and the parse
     * starts with 'Z', then {@code ZoneOffset.UTC} is selected.
     * The parser uses the {@linkplain #parseCaseInsensitive() case sensitive} setting.
     * <p>
     * For example, the following will parse:
     * <pre>
     *   "Europe/London"           -- ZoneId.of("Europe/London")
     *   "Z"                       -- ZoneOffset.UTC
     *   "UT"                      -- ZoneId.of("UT")
     *   "UTC"                     -- ZoneId.of("UTC")
     *   "GMT"                     -- ZoneId.of("GMT")
     *   "+01:30"                  -- ZoneOffset.of("+01:30")
     *   "UT+01:30"                -- ZoneOffset.of("+01:30")
     *   "UTC+01:30"               -- ZoneOffset.of("+01:30")
     *   "GMT+01:30"               -- ZoneOffset.of("+01:30")
     * </pre>
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @see #appendZoneRegionId()
     */
    public
    function appendZoneId()
    {
        $this->appendInternal(new ZoneIdPrinterParser(TemporalQueries::zoneId(), "ZoneId()"));
        return $this;
    }

    /**
     * Appends the time-zone region ID, such as 'Europe/Paris', to the formatter,
     * rejecting the zone ID if it is a {@code ZoneOffset}.
     * <p>
     * This appends an instruction to format/parse the zone ID to the builder
     * only if it is a region-based ID.
     * <p>
     * During formatting, the zone is obtained using a mechanism equivalent
     * to querying the temporal with {@link TemporalQueries#zoneId()}.
     * If the zone is a {@code ZoneOffset} or it cannot be obtained then
     * an exception is thrown unless the section of the formatter is optional.
     * If the zone is not an offset, then the zone will be printed using
     * the zone ID from {@link ZoneId#getId()}.
     * <p>
     * During parsing, the text must match a known zone or offset.
     * There are two types of zone ID, offset-based, such as '+01:30' and
     * region-based, such as 'Europe/London'. These are parsed differently.
     * If the parse starts with '+', '-', 'UT', 'UTC' or 'GMT', then the parser
     * expects an offset-based zone and will not match region-based zones.
     * The offset ID, such as '+02:30', may be at the start of the parse,
     * or prefixed by  'UT', 'UTC' or 'GMT'. The offset ID parsing is
     * equivalent to using {@link #appendOffset(String, String)} using the
     * arguments 'HH:MM:ss' and the no offset string '0'.
     * If the parse starts with 'UT', 'UTC' or 'GMT', and the parser cannot
     * match a following offset ID, then {@link ZoneOffset#UTC} is selected.
     * In all other cases, the list of known region-based zones is used to
     * find the longest available match. If no match is found, and the parse
     * starts with 'Z', then {@code ZoneOffset.UTC} is selected.
     * The parser uses the {@linkplain #parseCaseInsensitive() case sensitive} setting.
     * <p>
     * For example, the following will parse:
     * <pre>
     *   "Europe/London"           -- ZoneId.of("Europe/London")
     *   "Z"                       -- ZoneOffset.UTC
     *   "UT"                      -- ZoneId.of("UT")
     *   "UTC"                     -- ZoneId.of("UTC")
     *   "GMT"                     -- ZoneId.of("GMT")
     *   "+01:30"                  -- ZoneOffset.of("+01:30")
     *   "UT+01:30"                -- ZoneOffset.of("+01:30")
     *   "UTC+01:30"               -- ZoneOffset.of("+01:30")
     *   "GMT+01:30"               -- ZoneOffset.of("+01:30")
     * </pre>
     * <p>
     * Note that this method is identical to {@code appendZoneId()} except
     * in the mechanism used to obtain the zone.
     * Note also that parsing accepts offsets, whereas formatting will never
     * produce one.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @see #appendZoneId()
     */
    public
    function appendZoneRegionId()
    {
        $this->appendInternal(new ZoneIdPrinterParser(QUERY_REGION_ONLY, "ZoneRegionId()"));
        return $this;
    }

    /**
     * Appends the time-zone ID, such as 'Europe/Paris' or '+02:00', to
     * the formatter, using the best available zone ID.
     * <p>
     * This appends an instruction to format/parse the best available
     * zone or offset ID to the builder.
     * The zone ID is obtained in a lenient manner that first attempts to
     * find a true zone ID, such as that on {@code ZonedDateTime}, and
     * then attempts to find an offset, such as that on {@code OffsetDateTime}.
     * <p>
     * During formatting, the zone is obtained using a mechanism equivalent
     * to querying the temporal with {@link TemporalQueries#zone()}.
     * It will be printed using the result of {@link ZoneId#getId()}.
     * If the zone cannot be obtained then an exception is thrown unless the
     * section of the formatter is optional.
     * <p>
     * During parsing, the text must match a known zone or offset.
     * There are two types of zone ID, offset-based, such as '+01:30' and
     * region-based, such as 'Europe/London'. These are parsed differently.
     * If the parse starts with '+', '-', 'UT', 'UTC' or 'GMT', then the parser
     * expects an offset-based zone and will not match region-based zones.
     * The offset ID, such as '+02:30', may be at the start of the parse,
     * or prefixed by  'UT', 'UTC' or 'GMT'. The offset ID parsing is
     * equivalent to using {@link #appendOffset(String, String)} using the
     * arguments 'HH:MM:ss' and the no offset string '0'.
     * If the parse starts with 'UT', 'UTC' or 'GMT', and the parser cannot
     * match a following offset ID, then {@link ZoneOffset#UTC} is selected.
     * In all other cases, the list of known region-based zones is used to
     * find the longest available match. If no match is found, and the parse
     * starts with 'Z', then {@code ZoneOffset.UTC} is selected.
     * The parser uses the {@linkplain #parseCaseInsensitive() case sensitive} setting.
     * <p>
     * For example, the following will parse:
     * <pre>
     *   "Europe/London"           -- ZoneId.of("Europe/London")
     *   "Z"                       -- ZoneOffset.UTC
     *   "UT"                      -- ZoneId.of("UT")
     *   "UTC"                     -- ZoneId.of("UTC")
     *   "GMT"                     -- ZoneId.of("GMT")
     *   "+01:30"                  -- ZoneOffset.of("+01:30")
     *   "UT+01:30"                -- ZoneOffset.of("UT+01:30")
     *   "UTC+01:30"               -- ZoneOffset.of("UTC+01:30")
     *   "GMT+01:30"               -- ZoneOffset.of("GMT+01:30")
     * </pre>
     * <p>
     * Note that this method is identical to {@code appendZoneId()} except
     * in the mechanism used to obtain the zone.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @see #appendZoneId()
     */
    public
    function appendZoneOrOffsetId()
    {
        $this->appendInternal(new ZoneIdPrinterParser(TemporalQueries::zone(), "ZoneOrOffsetId()"));
        return $this;
    }

    /**
     * Appends the time-zone name, such as 'British Summer Time', to the formatter.
     * <p>
     * This appends an instruction to format/parse the textual name of the zone to
     * the builder.
     * <p>
     * During formatting, the zone is obtained using a mechanism equivalent
     * to querying the temporal with {@link TemporalQueries#zoneId()}.
     * If the zone is a {@code ZoneOffset} it will be printed using the
     * result of {@link ZoneOffset#getId()}.
     * If the zone is not an offset, the textual name will be looked up
     * for the locale set in the {@link DateTimeFormatter}.
     * If the temporal object being printed represents an instant, then the text
     * will be the summer or winter time text as appropriate.
     * If the lookup for text does not find any suitable result, then the
     * {@link ZoneId#getId() ID} will be printed instead.
     * If the zone cannot be obtained then an exception is thrown unless the
     * section of the formatter is optional.
     * <p>
     * During parsing, either the textual zone name, the zone ID or the offset
     * is accepted. Many textual zone names are not unique, such as CST can be
     * for both "Central Standard Time" and "China Standard Time". In this
     * situation, the zone id will be determined by the region information from
     * formatter's  {@link DateTimeFormatter#getLocale() locale} and the standard
     * zone id for that area, for example, America/New_York for the America Eastern
     * zone. The {@link #appendZoneText(TextStyle, Set)} may be used
     * to specify a set of preferred {@link ZoneId} in this situation.
     *
     * @param $textStyle TextStyle the text style to use, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendZoneText(TextStyle $textStyle)
    {
        $this->appendInternal(new ZoneTextPrinterParser($textStyle, null));
        return $this;
    }

    /**
     * Appends the time-zone name, such as 'British Summer Time', to the formatter.
     * <p>
     * This appends an instruction to format/parse the textual name of the zone to
     * the builder.
     * <p>
     * During formatting, the zone is obtained using a mechanism equivalent
     * to querying the temporal with {@link TemporalQueries#zoneId()}.
     * If the zone is a {@code ZoneOffset} it will be printed using the
     * result of {@link ZoneOffset#getId()}.
     * If the zone is not an offset, the textual name will be looked up
     * for the locale set in the {@link DateTimeFormatter}.
     * If the temporal object being printed represents an instant, then the text
     * will be the summer or winter time text as appropriate.
     * If the lookup for text does not find any suitable result, then the
     * {@link ZoneId#getId() ID} will be printed instead.
     * If the zone cannot be obtained then an exception is thrown unless the
     * section of the formatter is optional.
     * <p>
     * During parsing, either the textual zone name, the zone ID or the offset
     * is accepted. Many textual zone names are not unique, such as CST can be
     * for both "Central Standard Time" and "China Standard Time". In this
     * situation, the zone id will be determined by the region information from
     * formatter's  {@link DateTimeFormatter#getLocale() locale} and the standard
     * zone id for that area, for example, America/New_York for the America Eastern
     * zone. This method also allows a set of preferred {@link ZoneId} to be
     * specified for parsing. The matched preferred zone id will be used if the
     * textural zone name being parsed is not unique.
     * <p>
     * If the zone cannot be parsed then an exception is thrown unless the
     * section of the formatter is optional.
     *
     * @param $textStyle TextStyle the text style to use, not null
     * @param $preferredZones ZoneId[] the set of preferred zone ids, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendZoneText2(TextStyle $textStyle, $preferredZones)
    {
        $this->appendInternal(new ZoneTextPrinterParser($textStyle, $preferredZones));
        return $this;
    }

//-----------------------------------------------------------------------
    /**
     * Appends the chronology ID, such as 'ISO' or 'ThaiBuddhist', to the formatter.
     * <p>
     * This appends an instruction to format/parse the chronology ID to the builder.
     * <p>
     * During formatting, the chronology is obtained using a mechanism equivalent
     * to querying the temporal with {@link TemporalQueries#chronology()}.
     * It will be printed using the result of {@link Chronology#getId()}.
     * If the chronology cannot be obtained then an exception is thrown unless the
     * section of the formatter is optional.
     * <p>
     * During parsing, the chronology is parsed and must match one of the chronologies
     * in {@link Chronology#getAvailableChronologies()}.
     * If the chronology cannot be parsed then an exception is thrown unless the
     * section of the formatter is optional.
     * The parser uses the {@linkplain #parseCaseInsensitive() case sensitive} setting.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendChronologyId()
    {
        $this->appendInternal(new ChronoPrinterParser(null));
        return $this;
    }

    /**
     * Appends the chronology name to the formatter.
     * <p>
     * The calendar system name will be output during a format.
     * If the chronology cannot be obtained then an exception will be thrown.
     *
     * @param $textStyle TextStyle the text style to use, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendChronologyText(TextStyle $textStyle)
    {
        $this->appendInternal(new ChronoPrinterParser($textStyle));
        return $this;
    }

//-----------------------------------------------------------------------
    /**
     * Appends a localized date-time pattern to the formatter.
     * <p>
     * This appends a localized section to the builder, suitable for outputting
     * a date, time or date-time combination. The format of the localized
     * section is lazily looked up based on four items:
     * <ul>
     * <li>the {@code dateStyle} specified to this method
     * <li>the {@code timeStyle} specified to this method
     * <li>the {@code Locale} of the {@code DateTimeFormatter}
     * <li>the {@code Chronology}, selecting the best available
     * </ul>
     * During formatting, the chronology is obtained from the temporal object
     * being formatted, which may have been overridden by
     * {@link DateTimeFormatter#withChronology(Chronology)}.
     * <p>
     * During parsing, if a chronology has already been parsed, then it is used.
     * Otherwise the default from {@code DateTimeFormatter.withChronology(Chronology)}
     * is used, with {@code IsoChronology} as the fallback.
     * <p>
     * Note that this method provides similar functionality to methods on
     * {@code DateFormat} such as {@link java.text.DateFormat#getDateTimeInstance(int, int)}.
     *
     * @param $dateStyle FormatStyle the date style to use, null means no date required
     * @param $timeStyle FormatStyle the time style to use, null means no time required
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if both the date and time styles are null
     */
    public
    function appendLocalized(FormatStyle $dateStyle, FormatStyle $timeStyle)
    {
        if ($dateStyle == null && $timeStyle == null) {
            throw new IllegalArgumentException("Either the date or time style must be non-null");
        }
        $this->appendInternal(new LocalizedPrinterParser($dateStyle, $timeStyle));
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Appends a character literal to the formatter.
     * <p>
     * This character will be output during a format.
     *
     * @param $literal string the literal to append, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public function appendLiteral($literal)
    {
        $this->appendInternal(new CharLiteralPrinterParser($literal));
        return $this;
    }

    /**
     * Appends a string literal to the formatter.
     * <p>
     * This string will be output during a format.
     * <p>
     * If the literal is empty, nothing is added to the formatter.
     *
     * @param $literal string the literal to append, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public function appendLiteral2($literal)
    {
        if (strlen($literal)) {
            if (strlen($literal) === 1) {
                $this->appendInternal(new CharLiteralPrinterParser($literal[0]));
            } else {
                $this->appendInternal(new StringLiteralPrinterParser($literal));
            }
        }
        return $this;
    }

//-----------------------------------------------------------------------
    /**
     * Appends all the elements of a formatter to the builder.
     * <p>
     * This method has the same effect as appending each of the constituent
     * parts of the formatter directly to this builder.
     *
     * @param $formatter DateTimeFormatter the formatter to add, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function append(DateTimeFormatter $formatter)
    {
        $this->appendInternal($formatter->toPrinterParser(false));
        return $this;
    }

    /**
     * Appends a formatter to the builder which will optionally format/parse.
     * <p>
     * This method has the same effect as appending each of the constituent
     * parts directly to this builder surrounded by an {@link #optionalStart()} and
     * {@link #optionalEnd()}.
     * <p>
     * The formatter will format if data is available for all the fields contained within it.
     * The formatter will parse if the string matches, otherwise no error is returned.
     *
     * @param $formatter DateTimeFormatter the formatter to add, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public
    function appendOptional(DateTimeFormatter $formatter)
    {
        $this->appendInternal($formatter->toPrinterParser(true));
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Appends the elements defined by the specified pattern to the builder.
     * <p>
     * All letters 'A' to 'Z' and 'a' to 'z' are reserved as pattern letters.
     * The characters '#', '{' and '}' are reserved for future use.
     * The characters '[' and ']' indicate optional patterns.
     * The following pattern letters are defined:
     * <pre>
     *  Symbol  Meaning                     Presentation      Examples
     *  ------  -------                     ------------      -------
     *   G       era                         text              AD; Anno Domini; A
     *   u       year                        year              2004; 04
     *   y       year-of-era                 year              2004; 04
     *   D       day-of-year                 number            189
     *   M/L     month-of-year               number/text       7; 07; Jul; July; J
     *   d       day-of-month                number            10
     *
     *   Q/q     quarter-of-year             number/text       3; 03; Q3; 3rd quarter
     *   Y       week-based-year             year              1996; 96
     *   w       week-of-week-based-year     number            27
     *   W       week-of-month               number            4
     *   E       day-of-week                 text              Tue; Tuesday; T
     *   e/c     localized day-of-week       number/text       2; 02; Tue; Tuesday; T
     *   F       week-of-month               number            3
     *
     *   a       am-pm-of-day                text              PM
     *   h       clock-hour-of-am-pm (1-12)  number            12
     *   K       hour-of-am-pm (0-11)        number            0
     *   k       clock-hour-of-am-pm (1-24)  number            0
     *
     *   H       hour-of-day (0-23)          number            0
     *   m       minute-of-hour              number            30
     *   s       second-of-minute            number            55
     *   S       fraction-of-second          fraction          978
     *   A       milli-of-day                number            1234
     *   n       nano-of-second              number            987654321
     *   N       nano-of-day                 number            1234000000
     *
     *   V       time-zone ID                zone-id           America/Los_Angeles; Z; -08:30
     *   z       time-zone name              zone-name         Pacific Standard Time; PST
     *   O       localized zone-offset       offset-O          GMT+8; GMT+08:00; UTC-08:00;
     *   X       zone-offset 'Z' for zero    offset-X          Z; -08; -0830; -08:30; -083015; -08:30:15;
     *   x       zone-offset                 offset-x          +0000; -08; -0830; -08:30; -083015; -08:30:15;
     *   Z       zone-offset                 offset-Z          +0000; -0800; -08:00;
     *
     *   p       pad next                    pad modifier      1
     *
     *   '       escape for text             delimiter
     *   ''      single quote                literal           '
     *   [       optional section start
     *   ]       optional section end
     *   #       reserved for future use
     *   {       reserved for future use
     *   }       reserved for future use
     * </pre>
     * <p>
     * The count of pattern letters determine the format.
     * See <a href="DateTimeFormatter.html#patterns">DateTimeFormatter</a> for a user-focused description of the patterns.
     * The following tables define how the pattern letters map to the builder.
     * <p>
     * <b>Date fields</b>: Pattern letters to output a date.
     * <pre>
     *  Pattern  Count  Equivalent builder methods
     *  -------  -----  --------------------------
     *    G       1      appendText(ChronoField.ERA, TextStyle.SHORT)
     *    GG      2      appendText(ChronoField.ERA, TextStyle.SHORT)
     *    GGG     3      appendText(ChronoField.ERA, TextStyle.SHORT)
     *    GGGG    4      appendText(ChronoField.ERA, TextStyle.FULL)
     *    GGGGG   5      appendText(ChronoField.ERA, TextStyle.NARROW)
     *
     *    u       1      appendValue(ChronoField.YEAR, 1, 19, SignStyle.NORMAL);
     *    uu      2      appendValueReduced(ChronoField.YEAR, 2, 2000);
     *    uuu     3      appendValue(ChronoField.YEAR, 3, 19, SignStyle.NORMAL);
     *    u..u    4..n   appendValue(ChronoField.YEAR, n, 19, SignStyle.EXCEEDS_PAD);
     *    y       1      appendValue(ChronoField.YEAR_OF_ERA, 1, 19, SignStyle.NORMAL);
     *    yy      2      appendValueReduced(ChronoField.YEAR_OF_ERA, 2, 2000);
     *    yyy     3      appendValue(ChronoField.YEAR_OF_ERA, 3, 19, SignStyle.NORMAL);
     *    y..y    4..n   appendValue(ChronoField.YEAR_OF_ERA, n, 19, SignStyle.EXCEEDS_PAD);
     *    Y       1      append special localized WeekFields element for numeric week-based-year
     *    YY      2      append special localized WeekFields element for reduced numeric week-based-year 2 digits;
     *    YYY     3      append special localized WeekFields element for numeric week-based-year (3, 19, SignStyle.NORMAL);
     *    Y..Y    4..n   append special localized WeekFields element for numeric week-based-year (n, 19, SignStyle.EXCEEDS_PAD);
     *
     *    Q       1      appendValue(IsoFields.QUARTER_OF_YEAR);
     *    QQ      2      appendValue(IsoFields.QUARTER_OF_YEAR, 2);
     *    QQQ     3      appendText(IsoFields.QUARTER_OF_YEAR, TextStyle.SHORT)
     *    QQQQ    4      appendText(IsoFields.QUARTER_OF_YEAR, TextStyle.FULL)
     *    QQQQQ   5      appendText(IsoFields.QUARTER_OF_YEAR, TextStyle.NARROW)
     *    q       1      appendValue(IsoFields.QUARTER_OF_YEAR);
     *    qq      2      appendValue(IsoFields.QUARTER_OF_YEAR, 2);
     *    qqq     3      appendText(IsoFields.QUARTER_OF_YEAR, TextStyle.SHORT_STANDALONE)
     *    qqqq    4      appendText(IsoFields.QUARTER_OF_YEAR, TextStyle.FULL_STANDALONE)
     *    qqqqq   5      appendText(IsoFields.QUARTER_OF_YEAR, TextStyle.NARROW_STANDALONE)
     *
     *    M       1      appendValue(ChronoField.MONTH_OF_YEAR);
     *    MM      2      appendValue(ChronoField.MONTH_OF_YEAR, 2);
     *    MMM     3      appendText(ChronoField.MONTH_OF_YEAR, TextStyle.SHORT)
     *    MMMM    4      appendText(ChronoField.MONTH_OF_YEAR, TextStyle.FULL)
     *    MMMMM   5      appendText(ChronoField.MONTH_OF_YEAR, TextStyle.NARROW)
     *    L       1      appendValue(ChronoField.MONTH_OF_YEAR);
     *    LL      2      appendValue(ChronoField.MONTH_OF_YEAR, 2);
     *    LLL     3      appendText(ChronoField.MONTH_OF_YEAR, TextStyle.SHORT_STANDALONE)
     *    LLLL    4      appendText(ChronoField.MONTH_OF_YEAR, TextStyle.FULL_STANDALONE)
     *    LLLLL   5      appendText(ChronoField.MONTH_OF_YEAR, TextStyle.NARROW_STANDALONE)
     *
     *    w       1      append special localized WeekFields element for numeric week-of-year
     *    ww      2      append special localized WeekFields element for numeric week-of-year, zero-padded
     *    W       1      append special localized WeekFields element for numeric week-of-month
     *    d       1      appendValue(ChronoField.DAY_OF_MONTH)
     *    dd      2      appendValue(ChronoField.DAY_OF_MONTH, 2)
     *    D       1      appendValue(ChronoField.DAY_OF_YEAR)
     *    DD      2      appendValue(ChronoField.DAY_OF_YEAR, 2)
     *    DDD     3      appendValue(ChronoField.DAY_OF_YEAR, 3)
     *    F       1      appendValue(ChronoField.ALIGNED_DAY_OF_WEEK_IN_MONTH)
     *    E       1      appendText(ChronoField.DAY_OF_WEEK, TextStyle.SHORT)
     *    EE      2      appendText(ChronoField.DAY_OF_WEEK, TextStyle.SHORT)
     *    EEE     3      appendText(ChronoField.DAY_OF_WEEK, TextStyle.SHORT)
     *    EEEE    4      appendText(ChronoField.DAY_OF_WEEK, TextStyle.FULL)
     *    EEEEE   5      appendText(ChronoField.DAY_OF_WEEK, TextStyle.NARROW)
     *    e       1      append special localized WeekFields element for numeric day-of-week
     *    ee      2      append special localized WeekFields element for numeric day-of-week, zero-padded
     *    eee     3      appendText(ChronoField.DAY_OF_WEEK, TextStyle.SHORT)
     *    eeee    4      appendText(ChronoField.DAY_OF_WEEK, TextStyle.FULL)
     *    eeeee   5      appendText(ChronoField.DAY_OF_WEEK, TextStyle.NARROW)
     *    c       1      append special localized WeekFields element for numeric day-of-week
     *    ccc     3      appendText(ChronoField.DAY_OF_WEEK, TextStyle.SHORT_STANDALONE)
     *    cccc    4      appendText(ChronoField.DAY_OF_WEEK, TextStyle.FULL_STANDALONE)
     *    ccccc   5      appendText(ChronoField.DAY_OF_WEEK, TextStyle.NARROW_STANDALONE)
     * </pre>
     * <p>
     * <b>Time fields</b>: Pattern letters to output a time.
     * <pre>
     *  Pattern  Count  Equivalent builder methods
     *  -------  -----  --------------------------
     *    a       1      appendText(ChronoField.AMPM_OF_DAY, TextStyle.SHORT)
     *    h       1      appendValue(ChronoField.CLOCK_HOUR_OF_AMPM)
     *    hh      2      appendValue(ChronoField.CLOCK_HOUR_OF_AMPM, 2)
     *    H       1      appendValue(ChronoField.HOUR_OF_DAY)
     *    HH      2      appendValue(ChronoField.HOUR_OF_DAY, 2)
     *    k       1      appendValue(ChronoField.CLOCK_HOUR_OF_DAY)
     *    kk      2      appendValue(ChronoField.CLOCK_HOUR_OF_DAY, 2)
     *    K       1      appendValue(ChronoField.HOUR_OF_AMPM)
     *    KK      2      appendValue(ChronoField.HOUR_OF_AMPM, 2)
     *    m       1      appendValue(ChronoField.MINUTE_OF_HOUR)
     *    mm      2      appendValue(ChronoField.MINUTE_OF_HOUR, 2)
     *    s       1      appendValue(ChronoField.SECOND_OF_MINUTE)
     *    ss      2      appendValue(ChronoField.SECOND_OF_MINUTE, 2)
     *
     *    S..S    1..n   appendFraction(ChronoField.NANO_OF_SECOND, n, n, false)
     *    A       1      appendValue(ChronoField.MILLI_OF_DAY)
     *    A..A    2..n   appendValue(ChronoField.MILLI_OF_DAY, n)
     *    n       1      appendValue(ChronoField.NANO_OF_SECOND)
     *    n..n    2..n   appendValue(ChronoField.NANO_OF_SECOND, n)
     *    N       1      appendValue(ChronoField.NANO_OF_DAY)
     *    N..N    2..n   appendValue(ChronoField.NANO_OF_DAY, n)
     * </pre>
     * <p>
     * <b>Zone ID</b>: Pattern letters to output {@code ZoneId}.
     * <pre>
     *  Pattern  Count  Equivalent builder methods
     *  -------  -----  --------------------------
     *    VV      2      appendZoneId()
     *    z       1      appendZoneText(TextStyle.SHORT)
     *    zz      2      appendZoneText(TextStyle.SHORT)
     *    zzz     3      appendZoneText(TextStyle.SHORT)
     *    zzzz    4      appendZoneText(TextStyle.FULL)
     * </pre>
     * <p>
     * <b>Zone offset</b>: Pattern letters to output {@code ZoneOffset}.
     * <pre>
     *  Pattern  Count  Equivalent builder methods
     *  -------  -----  --------------------------
     *    O       1      appendLocalizedOffsetPrefixed(TextStyle.SHORT);
     *    OOOO    4      appendLocalizedOffsetPrefixed(TextStyle.FULL);
     *    X       1      appendOffset("+HHmm","Z")
     *    XX      2      appendOffset("+HHMM","Z")
     *    XXX     3      appendOffset("+HH:MM","Z")
     *    XXXX    4      appendOffset("+HHMMss","Z")
     *    XXXXX   5      appendOffset("+HH:MM:ss","Z")
     *    x       1      appendOffset("+HHmm","+00")
     *    xx      2      appendOffset("+HHMM","+0000")
     *    xxx     3      appendOffset("+HH:MM","+00:00")
     *    xxxx    4      appendOffset("+HHMMss","+0000")
     *    xxxxx   5      appendOffset("+HH:MM:ss","+00:00")
     *    Z       1      appendOffset("+HHMM","+0000")
     *    ZZ      2      appendOffset("+HHMM","+0000")
     *    ZZZ     3      appendOffset("+HHMM","+0000")
     *    ZZZZ    4      appendLocalizedOffset(TextStyle.FULL);
     *    ZZZZZ   5      appendOffset("+HH:MM:ss","Z")
     * </pre>
     * <p>
     * <b>Modifiers</b>: Pattern letters that modify the rest of the pattern:
     * <pre>
     *  Pattern  Count  Equivalent builder methods
     *  -------  -----  --------------------------
     *    [       1      optionalStart()
     *    ]       1      optionalEnd()
     *    p..p    1..n   padNext(n)
     * </pre>
     * <p>
     * Any sequence of letters not specified above, unrecognized letter or
     * reserved character will throw an exception.
     * Future versions may add to the set of patterns.
     * It is recommended to use single quotes around all characters that you want
     * to output directly to ensure that future changes do not break your application.
     * <p>
     * Note that the pattern string is similar, but not identical, to
     * {@link java.text.SimpleDateFormat SimpleDateFormat}.
     * The pattern string is also similar, but not identical, to that defined by the
     * Unicode Common Locale Data Repository (CLDR/LDML).
     * Pattern letters 'X' and 'u' are aligned with Unicode CLDR/LDML.
     * By contrast, {@code SimpleDateFormat} uses 'u' for the numeric day of week.
     * Pattern letters 'y' and 'Y' parse years of two digits and more than 4 digits differently.
     * Pattern letters 'n', 'A', 'N', and 'p' are added.
     * Number types will reject large numbers.
     *
     * @param $pattern string the pattern to add, not null
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if the pattern is invalid
     */
    public function appendPattern($pattern)
    {
        $this->parsePattern($pattern);
        return $this;
    }

    private function parsePattern($pattern)
    {
        for ($pos = 0;
             $pos < strlen($pattern);
             $pos++) {
            $cur = $pattern[$pos];
            if (($cur >= 'A' && $cur <= 'Z') || ($cur >= 'a' && $cur <= 'z')) {
                $start = $pos++;
                for (;
                    $pos < strlen($pattern) && $pattern[$pos] == $cur;
                    $pos++) ;  // short loop
                $count = $pos - $start;
                // padding
                if ($cur == 'p') {
                    $pad = 0;
                    if ($pos < strlen($pattern)) {
                        $cur = $pattern[$pos];
                        if (($cur >= 'A' && $cur <= 'Z') || ($cur >= 'a' && $cur <= 'z')) {
                            $pad = $count;
                            $start = $pos++;
                            for (;
                                $pos < strlen($pattern) && $pattern[$pos] == $cur;
                                $pos++) ;  // short loop
                            $count = $pos - $start;
                        }
                    }
                    if ($pad == 0) {
                        throw new IllegalArgumentException(
                            "Pad letter 'p' must be followed by valid pad pattern: " . $pattern);
                    }
                    $this->padNext($pad); // pad and continue parsing
                }
// main rules
                $field = self::$FIELD_MAP[$cur];
                if ($field != null) {
                    $this->parseField($cur, $count, $field);
                } else if ($cur == 'z') {
                    if ($count > 4) {
                        throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                    } else if ($count == 4) {
                        $this->appendZoneText(TextStyle::FULL());
                    } else {
                        $this->appendZoneText(TextStyle::SHORT());
                    }
                } else if ($cur == 'V') {
                    if ($count != 2) {
                        throw new IllegalArgumentException("Pattern letter count must be 2: " . $cur);
                    }
                    $this->appendZoneId();
                } else if ($cur == 'Z') {
                    if ($count < 4) {
                        $this->appendOffset("+HHMM", "+0000");
                    } else if ($count == 4) {
                        $this->appendLocalizedOffset(TextStyle::FULL());
                    } else if ($count == 5) {
                        $this->appendOffset("+HH:MM:ss", "Z");
                    } else {
                        throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                    }
                } else if ($cur == 'O') {
                    if ($count == 1) {
                        $this->appendLocalizedOffset(TextStyle::SHORT());
                    } else if ($count == 4) {
                        $this->appendLocalizedOffset(TextStyle::FULL());
                    } else {
                        throw new IllegalArgumentException("Pattern letter count must be 1 or 4: " . $cur);
                    }
                } else if ($cur == 'X') {
                    if ($count > 5) {
                        throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                    }
                    $this->appendOffset(OffsetIdPrinterParser::$PATTERNS[$count + ($count == 1 ? 0 : 1)], "Z");
                } else if ($cur == 'x') {
                    if ($count > 5) {
                        throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                    }
                    $zero = ($count == 1 ? "+00" : ($count % 2 == 0 ? "+0000" : "+00:00"));
                    $this->appendOffset(OffsetIdPrinterParser::$PATTERNS[$count + ($count == 1 ? 0 : 1)], $zero);
                } else if ($cur == 'W') {
                    // Fields defined by Locale
                    if ($count > 1) {
                        throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                    }
                    $this->appendInternal(new WeekBasedFieldPrinterParser($cur, $count));
                } else if ($cur == 'w') {
                    // Fields defined by Locale
                    if ($count > 2) {
                        throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                    }
                    $this->appendInternal(new WeekBasedFieldPrinterParser($cur, $count));
                } else if ($cur == 'Y') {
                    // Fields defined by Locale
                    $this->appendInternal(new WeekBasedFieldPrinterParser($cur, $count));
                } else {
                    throw new IllegalArgumentException("Unknown pattern letter: " . $cur);
                }
                $pos--;

            } else if ($cur == '\'') {
                // parse literals
                $start = $pos++;
                for (; $pos < strlen($pattern); $pos++) {
                    if ($pattern[$pos] === '\'') {
                        if ($pos + 1 < strlen($pattern) && $pattern[$pos + 1] == '\'') {
                            $pos++;
                        } else {
                            break;  // end of literal
                        }
                    }
                }
                if ($pos >= strlen($pattern)) {
                    throw new IllegalArgumentException("Pattern ends with an incomplete string literal: " . $pattern);
                }
                $str = substr($pattern, $start + 1, $pos);
                if (strlen($str) === 0) {
                    $this->appendLiteral('\'');
                } else {
                    $this->appendLiteral(str_replace($str, "''", "'"));
                }

            } else if ($cur == '[') {
                $this->optionalStart();

            } else if ($cur == ']') {
                if ($this->active->parent === null) {
                    throw new IllegalArgumentException("Pattern invalid as it contains ] without previous [");
                }
                $this->optionalEnd();

            } else if ($cur == '{' || $cur == '}' || $cur == '#') {
                throw new IllegalArgumentException("Pattern includes reserved character: '" . $cur . "'");
            } else {
                $this->appendLiteral($cur);
            }
        }
    }

    private function parseField($cur, $count, TemporalField $field)
    {
        $standalone = false;
        switch ($cur) {
            case 'u':
            case 'y':
                if ($count == 2) {
                    $this->appendValueReduced($field, 2, 2, ReducedPrinterParser::BASE_DATE());
                } else
                    if ($count < 4) {
                        $this->appendValue($field, $count, 19, SignStyle::NORMAL());
                    } else {
                        $this->appendValue($field, $count, 19, SignStyle::EXCEEDS_PAD());
                    }
                break;
            case
            'c':
                if ($count == 2) {
                    throw new IllegalArgumentException("Invalid pattern \"cc\"");
                }
            /*fallthrough*/
            case 'L':
            case 'q':
                $standalone = true;
            /*fallthrough*/
            case 'M':
            case 'Q':
            case 'E':
            case 'e':
                switch ($count) {
                    case 1:
                    case 2:
                        if ($cur == 'c' || $cur == 'e') {
                            $this->appendInternal(new WeekBasedFieldPrinterParser($cur, $count));
                        } else if ($cur == 'E') {
                            $this->appendText($field, TextStyle::SHORT());
                        } else {
                            if ($count == 1) {
                                $this->appendValue($field);
                            } else {
                                $this->appendValue($field, 2);
                            }
                        }
                        break;
                    case 3:
                        $this->appendText($field, $standalone ? TextStyle::SHORT_STANDALONE() : TextStyle::SHORT());
                        break;
                    case 4:
                        $this->appendText($field, $standalone ? TextStyle::FULL_STANDALONE() : TextStyle::FULL());
                        break;
                    case 5:
                        $this->appendText($field, $standalone ? TextStyle::NARROW_STANDALONE() : TextStyle::NARROW());
                        break;
                    default:
                        throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                }
                break;
            case 'a':
                if ($count == 1) {
                    $this->appendText($field, TextStyle::SHORT());
                } else {
                    throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                }
                break;
            case 'G':
                switch ($count) {
                    case 1:
                    case 2:
                    case 3:
                        $this->appendText($field, TextStyle::SHORT());
                        break;
                    case 4:
                        $this->appendText($field, TextStyle::FULL());
                        break;
                    case 5:
                        $this->appendText($field, TextStyle::NARROW());
                        break;
                    default:
                        throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                }
                break;
            case 'S':
                $this->appendFraction(ChronoField::NANO_OF_SECOND(), $count, $count, false);
                break;
            case 'F':
                if ($count == 1) {
                    $this->appendValue($field);
                } else {
                    throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                }
                break;
            case 'd':
            case 'h':
            case 'H':
            case 'k':
            case 'K':
            case 'm':
            case 's':
                if ($count == 1) {
                    $this->appendValue($field);
                } else if ($count == 2) {
                    $this->appendValue($field, $count);
                } else {
                    throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                }
                break;
            case 'D':
                if ($count == 1) {
                    $this->appendValue($field);
                } else if ($count <= 3) {
                    $this->appendValue($field, $count);
                } else {
                    throw new IllegalArgumentException("Too many pattern letters: " . $cur);
                }
                break;
            default:
                if ($count == 1) {
                    $this->appendValue($field);
                } else {
                    $this->appendValue($field, $count);
                }
                break;
        }
    }

    private static function init()
    {
        self::$FIELD_MAP = [
            // SDF = SimpleDateFormat
            'G' => ChronoField::ERA(),                       // SDF, LDML (different to both for 1/2 chars)
            'y' => ChronoField::YEAR_OF_ERA(),               // SDF, LDML
            'u' => ChronoField::YEAR(),                      // LDML (different in SDF)
            'Q' => IsoFields::QUARTER_OF_YEAR(),             // LDML (removed quarter from 310)
            'q' => IsoFields::QUARTER_OF_YEAR(),             // LDML (stand-alone)
            'M' => ChronoField::MONTH_OF_YEAR(),             // SDF, LDML
            'L' => ChronoField::MONTH_OF_YEAR(),             // SDF, LDML (stand-alone)
            'D' => ChronoField::DAY_OF_YEAR(),               // SDF, LDML
            'd' => ChronoField::DAY_OF_MONTH(),              // SDF, LDML
            'F' => ChronoField::ALIGNED_DAY_OF_WEEK_IN_MONTH(),  // SDF, LDML
            'E' => ChronoField::DAY_OF_WEEK(),               // SDF, LDML (different to both for 1/2 chars)
            'c' => ChronoField::DAY_OF_WEEK(),               // LDML (stand-alone)
            'e' => ChronoField::DAY_OF_WEEK(),               // LDML (needs localized week number)
            'a' => ChronoField::AMPM_OF_DAY(),               // SDF, LDML
            'H' => ChronoField::HOUR_OF_DAY(),               // SDF, LDML
            'k' => ChronoField::CLOCK_HOUR_OF_DAY(),         // SDF, LDML
            'K' => ChronoField::HOUR_OF_AMPM(),              // SDF, LDML
            'h' => ChronoField::CLOCK_HOUR_OF_AMPM(),        // SDF, LDML
            'm' => ChronoField::MINUTE_OF_HOUR(),            // SDF, LDML
            's' => ChronoField::SECOND_OF_MINUTE(),          // SDF, LDML
            'S' => ChronoField::NANO_OF_SECOND(),            // LDML (SDF uses milli-of-second number)
            'A' => ChronoField::MILLI_OF_DAY(),              // LDML
            'n' => ChronoField::NANO_OF_SECOND(),            // 310 (proposed for LDML)
            'N' => ChronoField::NANO_OF_DAY(),               // 310 (proposed for LDML)
            // 310 - z - time-zone names, matches LDML and SimpleDateFormat 1 to 4
            // 310 - Z - matches SimpleDateFormat and LDML
            // 310 - V - time-zone id, matches LDML
            // 310 - p - prefix for padding
            // 310 - X - matches LDML, almost matches SDF for 1, exact match 2&3, extended 4&5
            // 310 - x - matches LDML
            // 310 - w, W, and Y are localized forms matching LDML
            // LDML - U - cycle year name, not supported by 310 yet
            // LDML - l - deprecated
            // LDML - j - not relevant
            // LDML - g - modified-julian-day
            // LDML - v,V - extended time-zone names
        ];
    }

    /** Map of letters to fields. */
    private static $FIELD_MAP;

//-----------------------------------------------------------------------
    /**
     * Causes the next added printer/parser to pad to a fixed width using a space.
     * <p>
     * This padding will pad to a fixed width using spaces.
     * <p>
     * During formatting, the decorated element will be output and then padded
     * to the specified width. An exception will be thrown during formatting if
     * the pad width is exceeded.
     * <p>
     * During parsing, the padding and decorated element are parsed.
     * If parsing is lenient, then the pad width is treated as a maximum.
     * The padding is parsed greedily. Thus, if the decorated element starts with
     * the pad character, it will not be parsed.
     *
     * @param $padWidth int the pad width, 1 or greater
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if pad width is too small
     */
    public
    function padNext($padWidth)
    {
        return $this->padNext($padWidth, ' ');
    }

    /**
     * Causes the next added printer/parser to pad to a fixed width.
     * <p>
     * This padding is intended for padding other than zero-padding.
     * Zero-padding should be achieved using the appendValue methods.
     * <p>
     * During formatting, the decorated element will be output and then padded
     * to the specified width. An exception will be thrown during formatting if
     * the pad width is exceeded.
     * <p>
     * During parsing, the padding and decorated element are parsed.
     * If parsing is lenient, then the pad width is treated as a maximum.
     * If parsing is case insensitive, then the pad character is matched ignoring case.
     * The padding is parsed greedily. Thus, if the decorated element starts with
     * the pad character, it will not be parsed.
     *
     * @param $padWidth int the pad width, 1 or greater
     * @param $padChar int the pad character
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalArgumentException if pad width is too small
     */
    public
    function padNext2($padWidth, $padChar)
    {
        if ($padWidth < 1) {
            throw new IllegalArgumentException("The pad width must be at least one but was " . $padWidth);
        }

        $this->active->padNextWidth = $padWidth;
        $this->active->padNextChar = $padChar;
        $this->active->valueParserIndex = -1;
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Mark the start of an optional section.
     * <p>
     * The output of formatting can include optional sections, which may be nested.
     * An optional section is started by calling this method and ended by calling
     * {@link #optionalEnd()} or by ending the build process.
     * <p>
     * All elements in the optional section are treated as optional.
     * During formatting, the section is only output if data is available in the
     * {@code TemporalAccessor} for all the elements in the section.
     * During parsing, the whole section may be missing from the parsed string.
     * <p>
     * For example, consider a builder setup as
     * {@code builder.appendValue(HOUR_OF_DAY,2).optionalStart().appendValue(MINUTE_OF_HOUR,2)}.
     * The optional section ends automatically at the end of the builder.
     * During formatting, the minute will only be output if its value can be obtained from the date-time.
     * During parsing, the input will be successfully parsed whether the minute is present or not.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     */
    public function optionalStart()
    {
        $this->active->valueParserIndex = -1;
        $this->active = new DateTimeFormatterBuilder($this->active, true);
        return $this;
    }

    /**
     * Ends an optional section.
     * <p>
     * The output of formatting can include optional sections, which may be nested.
     * An optional section is started by calling {@link #optionalStart()} and ended
     * using this method (or at the end of the builder).
     * <p>
     * Calling this method without having previously called {@code optionalStart}
     * will throw an exception.
     * Calling this method immediately after calling {@code optionalStart} has no effect
     * on the formatter other than ending the (empty) optional section.
     * <p>
     * All elements in the optional section are treated as optional.
     * During formatting, the section is only output if data is available in the
     * {@code TemporalAccessor} for all the elements in the section.
     * During parsing, the whole section may be missing from the parsed string.
     * <p>
     * For example, consider a builder setup as
     * {@code builder.appendValue(HOUR_OF_DAY,2).optionalStart().appendValue(MINUTE_OF_HOUR,2).optionalEnd()}.
     * During formatting, the minute will only be output if its value can be obtained from the date-time.
     * During parsing, the input will be successfully parsed whether the minute is present or not.
     *
     * @return DateTimeFormatterBuilder this, for chaining, not null
     * @throws IllegalStateException if there was no previous call to {@code optionalStart}
     */
    public
    function optionalEnd()
    {
        if ($this->active->parent == null) {
            throw new IllegalStateException("Cannot call optionalEnd() as there was no previous call to optionalStart()");
        }

        if (count($this->active->printerParsers) > 0) {
            $cpp = new CompositePrinterParser($this->active->printerParsers, $this->active->optional);
            $this->active = $this->active->parent;
            $this->appendInternal($cpp);
        } else {
            $this->active = $this->active->parent;
        }
        return $this;
    }

    //-----------------------------------------------------------------------
    /**
     * Appends a printer and/or parser to the internal list handling padding.
     *
     * @param $pp DateTimePrinterParser the printer-parser to add, not null
     * @return int the index into the active parsers list
     */
    private function appendInternal(DateTimePrinterParser $pp)
    {
        if ($this->active->padNextWidth > 0) {
            if ($pp != null) {
                $pp = new PadPrinterParserDecorator($pp, $this->active->padNextWidth, $this->active->padNextChar);
            }
            $this->active->padNextWidth = 0;
            $this->active->padNextChar = 0;
        }
        $this->active->printerParsers[] = $pp;
        $this->active->valueParserIndex = -1;
        return count($this->active->printerParsers) - 1;
    }

    //-----------------------------------------------------------------------
    /**
     * Completes this builder by creating the {@code DateTimeFormatter}
     * using the default locale.
     * <p>
     * This will create a formatter with the {@linkplain Locale#getDefault(Locale.Category) default FORMAT locale}.
     * Numbers will be printed and parsed using the standard DecimalStyle.
     * The resolver style will be {@link ResolverStyle#SMART SMART}.
     * <p>
     * Calling this method will end any open optional sections by repeatedly
     * calling {@link #optionalEnd()} before creating the formatter.
     * <p>
     * This builder can still be used after creating the formatter if desired,
     * although the state may have been changed by calls to {@code optionalEnd}.
     *
     * @return DateTimeFormatter the created formatter, not null
     */
    public function toFormatter()
    {
        return $this->toFormatter(Locale::getDefault(Locale::$Category->FORMAT));
    }

    /**
     * Completes this builder by creating the {@code DateTimeFormatter}
     * using the specified locale.
     * <p>
     * This will create a formatter with the specified locale.
     * Numbers will be printed and parsed using the standard DecimalStyle.
     * The resolver style will be {@link ResolverStyle#SMART SMART}.
     * <p>
     * Calling this method will end any open optional sections by repeatedly
     * calling {@link #optionalEnd()} before creating the formatter.
     * <p>
     * This builder can still be used after creating the formatter if desired,
     * although the state may have been changed by calls to {@code optionalEnd}.
     *
     * @param $locale Locale the locale to use for formatting, not null
     * @return DateTimeFormatter the created formatter, not null
     */
    public
    function toFormatter2(Locale $locale)
    {
        return $this->toFormatter($locale, ResolverStyle::SMART(), null);
    }

    /**
     * Completes this builder by creating the formatter.
     * This uses the default locale.
     *
     * @param $resolverStyle  the resolver style to use, not null
     * @return the created formatter, not null
     */
    public function toFormatter3(ResolverStyle $resolverStyle, Chronology $chrono)
    {
        return $this->toFormatter(Locale::getDefault(Locale::$Category->FORMAT), $resolverStyle, $chrono);
    }

    /**
     * Completes this builder by creating the formatter.
     *
     * @param $locale Locale the locale to use for formatting, not null
     * @param $chrono Chronology the chronology to use, may be null
     * @return DateTimeFormatter the created formatter, not null
     */
    private function toFormatter4(Locale $locale, ResolverStyle $resolverStyle, Chronology $chrono)
    {
        while ($this->active->parent != null) {
            $this->optionalEnd();
        }

        $pp = new CompositePrinterParser($this->printerParsers, false);
        return new DateTimeFormatter($pp, $locale, DecimalStyle::STANDARD(),
            $resolverStyle, null, $chrono, null);
    }

    /**
     * Length comparator.
     * TODO
     */
/*static final Comparator<String > LENGTH_SORT = new Comparator < String>()
{
@Override
public int compare(String str1, String str2)
{
return str1->length() == str2->length() ? str1->compareTo(str2) : str1->length() - str2->length();
}
};*/
}
