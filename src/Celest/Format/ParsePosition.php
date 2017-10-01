<?php declare(strict_types=1);
/*
 * Copyright (c) 1996, 2013, Oracle and/or its affiliates. All rights reserved.
 * ORACLE PROPRIETARY/CONFIDENTIAL. Use is subject to license terms.
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 */

/*
 * (C) Copyright Taligent, Inc. 1996, 1997 - All Rights Reserved
 * (C) Copyright IBM Corp. 1996 - 1998 - All Rights Reserved
 *
 *   The original version of this source code and documentation is copyrighted
 * and owned by Taligent, Inc., a wholly-owned subsidiary of IBM. These
 * materials are provided under terms of a License Agreement between Taligent
 * and Sun. This technology is protected by multiple US and International
 * patents. This notice and attribution to Taligent may not be removed.
 *   Taligent is a registered trademark of Taligent, Inc.
 *
 */

namespace Celest\Format;

/**
 * <code>ParsePosition</code> is a simple class used by <code>Format</code>
 * and its subclasses to keep track of the current position during parsing.
 * The <code>parseObject</code> method in the various <code>Format</code>
 * classes requires a <code>ParsePosition</code> object as an argument.
 *
 * <p>
 * By design, as you parse through a string with different formats,
 * you can use the same <code>ParsePosition</code>, since the index parameter
 * records the current position.
 *
 * @author      Mark Davis
 * @see         java.text.Format
 */
class ParsePosition
{

    /**
     * Input: the place you start parsing.
     * <br>Output: position where the parse stopped.
     * This is designed to be used serially,
     * with each call setting index up for the next one.
     */
    private $index = 0;
    private $errorIndex = -1;

    /**
     * Retrieve the current parse position.  On input to a parse method, this
     * is the index of the character at which parsing will begin; on output, it
     * is the index of the character following the last character parsed.
     *
     * @return int the current parse position
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Set the current parse position.
     *
     * @param int $index the current parse position
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    /**
     * Create a new ParsePosition with the given initial index.
     *
     * @param int $index initial index
     */
    public function __construct($index)
    {
        $this->index = $index;
    }

    /**
     * Set the index at which a parse error occurred.  Formatters
     * should set this before returning an error code from their
     * parseObject method.  The default value is -1 if this is not set.
     *
     * @param int $ei the index at which an error occurred
     * @since 1.2
     */
    public function setErrorIndex($ei)
    {
        $this->errorIndex = $ei;
    }

    /**
     * Retrieve the index at which an error occurred, or -1 if the
     * error index has not been set.
     *
     * @return int the index at which an error occurred
     * @since 1.2
     */
    public function getErrorIndex()
    {
        return $this->errorIndex;
    }

    /**
     * Return a string representation of this ParsePosition.
     * @return string a string representation of this object
     */
    public function __toString()
    {
        return get_class() .
        "[index=" . $this->index .
        ",errorIndex=" . $this->errorIndex . ']';
    }
}
