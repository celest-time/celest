<?php

namespace Celest\Temporal;


class FieldValues implements \Iterator
{
    /** @var array */
    private $fieldValues = [];
    /**
     * @param TemporalField $field
     * @param int $value
     * @return int|null
     */
    public function put(TemporalField $field, $value) {
        $prev = @$this->fieldValues[$field->__toString()];

        $this->fieldValues[$field->__toString()] = [$field, $value];

        return $prev !== null ? $prev[1] : null;
    }

    /**
     * @param TemporalField $field
     * @return int
     */
    public function remove(TemporalField $field) {
        $prev = @$this->fieldValues[$field->__toString()];

        unset($this->fieldValues[$field->__toString()]);

        return $prev !== null ? $prev[1] : null;
    }

    /**
     * @param TemporalField $field
     * @return bool
     */
    public function has(TemporalField $field) {
        return isset($this->fieldValues[$field->__toString()]);
    }

    /**
     * @param TemporalField $field
     * @return int|null
     */
    public function get(TemporalField $field)
    {
        $val = @$this->fieldValues[$field->__toString()];
        return $val !== null ? $val[1] : null;
    }

    /**
     * @return int
     */
    public function current()
    {
        return current($this->fieldValues)[1];
    }

    public function next()
    {
        next($this->fieldValues);
    }

    /**
     * @return TemporalField
     */
    public function key()
    {
        return current($this->fieldValues)[0];
    }

    public function valid()
    {
        return current($this->fieldValues) !== false;
    }

    public function rewind()
    {
        return reset($this->fieldValues);
    }

    /**
     * return bool
     */
    public function isEmpty()
    {
        return count($this->fieldValues) === 0;
    }

    public function __toString()
    {
        $buf = '[';
        $sep = '';
        foreach ($this->fieldValues as $entry) {
            $buf .= $entry[0] . '=' . $entry[1] . $sep;
            $sep = ',';
        }
        return $buf . ']';
    }
}