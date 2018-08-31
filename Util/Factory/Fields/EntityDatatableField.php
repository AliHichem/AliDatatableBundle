<?php

namespace Ali\DatatableBundle\Util\Factory\Fields;

/**
 * The datatable field for entities, to be used in the Datatable->setFields method.
 *
 * Class EntityDatatableField
 * @package Ali\DatatableBundle\Util\Factory\Fields
 */
class EntityDatatableField extends DatatableField
{
    /** @var array */
    protected $entity_fields;

    public function __construct($field, array $entity_fields)
    {
        parent::__construct($field);
        $this->entity_fields = $entity_fields;
    }

    public function __toString()
    {
        return $this->field;
    }

    /**
     * @return array
     */
    public function getEntityFields()
    {
        return $this->entity_fields;
    }

    /**
     * @param array $entity_fields
     * @return EntityDatatableField
     */
    public function setEntityFields($entity_fields)
    {
        $this->entity_fields = $entity_fields;

        return $this;
    }
}