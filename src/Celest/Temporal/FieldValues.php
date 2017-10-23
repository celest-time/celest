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
    public function put(TemporalField $field, $value)
    {
        $field_str = $field->__toString();

        if(isset($this->fieldValues[$field_str])) {
            $prev = $this->fieldValues[$field_str];
        } else {
            $prev = null;
        }

        $this->fieldValues[$field_str] = [$field, $value];

        return $prev !== null ? $prev[1] : null;
    }

    /**
     * @param TemporalField $field
     * @return int
     */
    public function remove(TemporalField $field)
    {
        $field_str = $field->__toString();

        if(isset($this->fieldValues[$field_str])) {
            $prev = $this->fieldValues[$field_str];
        } else {
            $prev = null;
        }

        unset($this->fieldValues[$field->__toString()]);

        return $prev !== null ? $prev[1] : null;
    }

    /**
     * @param TemporalField $field
     * @return bool
     */
    public function has(TemporalField $field)
    {
        return isset($this->fieldValues[$field->__toString()]);
    }

    /**
     * @param TemporalField $field
     * @return int|null
     */
    public function get(TemporalField $field)
    {
        $field_str = $field->__toString();

        if(isset($this->fieldValues[$field_str])) {
            return $this->fieldValues[$field_str][1];
        } else {
            return null;
        }
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

    public function size()
    {
        return count($this->fieldValues);
    }

    /**
     * @param TemporalField[] $resolverFields
     */
    public function filter(array $resolverFields)
    {
        $fields = [];
        foreach ($resolverFields as $field) {
            if ($field instanceof TemporalField)
                $fields[$field->__toString()] = null;
        }

        $this->fieldValues = \array_intersect_key($this->fieldValues, $fields);
    }
}