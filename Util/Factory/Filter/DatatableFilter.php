<?php

namespace Ali\DatatableBundle\Util\Factory\Filter;

/**
 * Class DatatableFilter
 *
 * @author Maarten Sprakel <maarten@extendas.com>
 */
class DatatableFilter
{
    const SEARCH_TYPE_LIKE = 'like';
    const SEARCH_TYPE_EQUALS = 'equals';

    /** @var DatatableFilterValue[] */
    protected $filter_values = array();
    protected $search_type;
    protected $default_value;

    /**
     * DatatableFilter constructor.
     * @param array $filter_values
     * @param string $search_type
     * @param mixed $default_value
     */
    public function __construct(array $filter_values, $search_type=self::SEARCH_TYPE_EQUALS, $default_value = null)
    {
        $this->filter_values = $filter_values;
        $this->search_type = $search_type;
        $this->default_value = $default_value;
    }

    /**
     * adds a datatable filter value
     *
     * @param DatatableFilterValue $filter_value
     * @return $this
     */
    public function addFilterValue(DatatableFilterValue $filter_value)
    {
        $this->filter_values[] = $filter_value;
        return $this;
    }
    /**
     * sets the datatable filter values
     *
     * @param DatatableFilterValue[] $filter_values
     * @return $this
     */
    public function setFilterValues(array $filter_values)
    {
        $this->filter_values = $filter_values;
        return $this;
    }

    /**
     * @param string $search_type
     */
    public function setSearchType($search_type)
    {
        $this->search_type = $search_type;
    }

    /**
     * @return string
     */
    public function getSearchType()
    {
        return $this->search_type;
    }

    /**
     * @return DatatableFilterValue[]|array
     */
    public function getFilterValues()
    {
        return $this->filter_values;
    }

    /**
     * @return mixed|null
     */
    public function getDefaultValue()
    {
        return $this->default_value;
    }

    /**
     * @param mixed|null $default_value
     * @return DatatableFilter
     */
    public function setDefaultValue($default_value)
    {
        $this->default_value = $default_value;
        return $this;
    }

    /**
     * Static helper to easily create boolean filter
     *
     * @param string $yes_label
     * @param string $no_label
     * @param bool|null $default_value
     * @return DatatableFilter
     */
    public static function constructBooleanFilter($yes_label = 'yes', $no_label = 'no', $default_value = null)
    {
        return new self(
            array(
                new DatatableFilterValue(1, $yes_label),
                new DatatableFilterValue(0, $no_label)
            ),
            self::SEARCH_TYPE_EQUALS,
            (int)$default_value
        );
    }

    /**
     * Static helper to create filter for array of entities
     *
     * @param array $entities
     * @param string $getter
     * @return DatatableFilter
     */
    public static function constructEntityFilter(array $entities, $getter='__toString()')
    {
        $filters = [];
        foreach ($entities as $entity)
        {
            $value = $entity->$getter();
            $filters[] = new DatatableFilterValue($value, $value);
        }
        return new self($filters);
    }
}