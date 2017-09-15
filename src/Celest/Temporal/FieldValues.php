<?php declare(strict_types=1);

namespace Celest\Temporal;

/**
 * Class FieldValues
 * @package Celest\Temporal
 */
class FieldValues implements \Iterator
{
    /** @var array */
    private $fieldValues = [];

    /**
     * @param TemporalField $field
     * @param int $value
     * @return int|null
     */
    public function put(TemporalField $field, int $value) : ?int
    {
        $prev = @$this->fieldValues[$field->__toString()];

        $this->fieldValues[$field->__toString()] = [$field, $value];

        return $prev !== null ? $prev[1] : null;
    }

    /**
     * @param TemporalField $field
     * @return int
     */
    public function remove(TemporalField $field) : ?int
    {
        $prev = @$this->fieldValues[$field->__toString()];

        unset($this->fieldValues[$field->__toString()]);

        return $prev !== null ? $prev[1] : null;
    }

    /**
     * @param TemporalField $field
     * @return bool
     */
    public function has(TemporalField $field) : bool
    {
        return isset($this->fieldValues[$field->__toString()]);
    }

    /**
     * @param TemporalField $field
     * @return int|null
     */
    public function get(TemporalField $field) : ?int
    {
        $val = @$this->fieldValues[$field->__toString()];
        return $val !== null ? $val[1] : null;
    }

    /**
     * @return int
     */
    public function current() : int
    {
        return current($this->fieldValues)[1];
    }

    public function next() : void
    {
        next($this->fieldValues);
    }

    /**
     * @return TemporalField
     */
    public function key() : TemporalField
    {
        return current($this->fieldValues)[0];
    }

    public function valid() : bool
    {
        return current($this->fieldValues) !== false;
    }

    public function rewind() : void
    {
        reset($this->fieldValues);
    }

    /**
     * return bool
     */
    public function isEmpty() : bool
    {
        return count($this->fieldValues) === 0;
    }

    public function __toString() : string
    {
        $buf = '[';
        $sep = '';
        foreach ($this->fieldValues as $entry) {
            $buf .= $entry[0] . '=' . $entry[1] . $sep;
            $sep = ',';
        }
        return $buf . ']';
    }

    public function size() : int
    {
        return count($this->fieldValues);
    }

    /**
     * @param TemporalField[] $resolverFields
     */
    public function filter(array $resolverFields) : void
    {
        $fields = [];
        foreach ($resolverFields as $field) {
            if ($field instanceof TemporalField)
                $fields[$field->__toString()] = null;
        }

        $this->fieldValues = \array_intersect_key($this->fieldValues, $fields);
    }
}