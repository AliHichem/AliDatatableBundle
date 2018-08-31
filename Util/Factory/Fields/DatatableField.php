<?php

namespace Ali\DatatableBundle\Util\Factory\Fields;

/**
 * The default datatable field to be used in the Datatable->setFields method.
 *
 * Class DatatableField
 * @package Ali\DatatableBundle\Util\Factory\Fields
 */
class DatatableField
{
    /** @var string */
    protected $field;

    public function __construct($field)
    {
        $this->field = $field;
    }

    public function __toString()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @return DatatableField
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }
}