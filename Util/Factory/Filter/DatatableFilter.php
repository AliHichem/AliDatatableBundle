<?php

namespace Ali\DatatableBundle\Util\Factory\Filter;

class DatatableFilter
{
    /** @var DatatableFilterValue[] */
    protected $filter_values = array();

    public function __construct(array $filter_values)
    {
        $this->filter_values = $filter_values;
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
     * @return DatatableFilterValue[]|array
     */
    public function getFilterValues()
    {
        return $this->filter_values;
    }

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