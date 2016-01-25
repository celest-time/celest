<?php

namespace Php\Time\Format\Builder;

use Php\Time\Format\DateTimePrintContext;
use Php\Time\DateTimeException;
use Php\Time\Format\DateTimeParseContext;

/**
 * Strategy for formatting/parsing date-time information.
 * <p>
 * The printer may format any part, or the whole, of the input date-time object.
 * Typically, a complete format is constructed from a number of smaller
 * units, each outputting a single field.
 * <p>
 * The parser may parse any piece of text from the input, storing the result
 * in the context. Typically, each individual parser will just parse one
 * field, such as the day-of-month, storing the value in the context.
 * Once the parse is complete, the caller will then resolve the parsed values
 * to create the desired object, such as a {@code LocalDate}.
 * <p>
 * The parse position will be updated during the parse. Parsing will start at
 * the specified index and the return value specifies the new parse position
 * for the next parser. If an error occurs, the returned index will be negative
 * and will have the error position encoded using the complement operator.
 *
 * @implSpec
 * This interface must be implemented with care to ensure other classes operate correctly.
 * All implementations that can be instantiated must be final, immutable and thread-safe.
 * <p>
 * The context is not a thread-safe object and a new instance will be created
 * for each format that occurs. The context must not be stored in an instance
 * variable or shared with any other threads.
 */
interface DateTimePrinterParser
{

    /**
     * Prints the date-time object to the buffer.
     * <p>
     * The context holds information to use during the format.
     * It also contains the date-time information to be printed.
     * <p>
     * The buffer must not be mutated beyond the content controlled by the implementation.
     *
     * @param DateTimePrintContext $context the context to format using, not null
     * @param string $buf the buffer to append to, not null
     * @return bool false if unable to query the value from the date-time, true otherwise
     * @throws DateTimeException if the date-time cannot be printed successfully
     */
    function format(DateTimePrintContext $context, &$buf);

    /**
     * Parses text into date-time information.
     * <p>
     * The context holds information to use during the parse.
     * It is also used to store the parsed date-time information.
     *
     * @param DateTimeParseContext $context the context to use and parse into, not null
     * @param string $text the input text to parse, not null
     * @param int $position the position to start parsing at, from 0 to the text length
     * @return int the new parse position, where negative means an error with the
     *  error position encoded using the complement ~ operator
     * @throws NullPointerException if the context or text is null
     * @throws IndexOutOfBoundsException if the position is invalid
     */
    function parse(DateTimeParseContext $context, $text, $position);

    function __toString();
}