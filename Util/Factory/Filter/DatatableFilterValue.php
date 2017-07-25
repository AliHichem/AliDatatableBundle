<?php

namespace Ali\DatatableBundle\Util\Factory\Filter;

use Ali\DatatableBundle\Util\Exceptions\InvalidFilterValueLabelException;

class DatatableFilterValue
{
    protected $value;

    /** @var string */
    protected $label;

    public function __construct($value, $label)
    {
        if (!is_string($label))
        {
            throw new InvalidFilterValueLabelException($label);
        }
        $this->value = $value;
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return DatatableFilterValue
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return DatatableFilterValue
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }


}