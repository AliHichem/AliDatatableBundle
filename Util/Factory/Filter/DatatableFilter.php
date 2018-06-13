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

    /**
     * DatatableFilter constructor.
     * @param array $filter_values
     * @param string $search_type
     */
    public function __construct(array $filter_values, $search_type=self::SEARCH_TYPE_EQUALS)
    {
        $this->filter_values = $filter_values;
        $this->search_type = $search_type;
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
     * Static helper to easily create boolean filter
     *
     * @param string $yes_label
     * @param string $no_label
     * @return DatatableFilter
     */
    public static function constructBooleanFilter($yes_label = 'yes', $no_label = 'no')
    {
        return new DatatableFilter(
            array(
                new DatatableFilterValue(1, $yes_label),
                new DatatableFilterValue(0, $no_label)
            )
        );
    }
}