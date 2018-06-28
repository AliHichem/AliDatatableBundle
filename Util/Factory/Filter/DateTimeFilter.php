<?php

namespace Ali\DatatableBundle\Util\Factory\Filter;

/**
 * Class DateTimeFilter
 *
 * Note: this Filter is unfortunately not plug-and-play. For an example of how it can be used, see:
 * https://github.com/Extendas/SPIN/blob/develop/apps/spin/Resources/AliDatatableBundle/views/Internal/script.html.twig
 * and
 * https://github.com/Extendas/SPIN/blob/develop/src/Extendas/SpinBundle/Resources/views/Main/Datatable/table_layout.html.twig
 *
 * @author Rein Baarsma <rein@solidwebcode.com>
 */
class DateTimeFilter extends DatatableFilter
{
    protected $is_filter_time = false;
    protected $is_required = false;
    protected $default_start;
    protected $default_end;

    /**
     * DateTimeFilter constructor.
     * @param bool $is_filter_time
     * @param bool $is_required
     * @param \DateTime $default_start
     * @param \DateTime $default_end
     */
    public function __construct($is_filter_time=false, $is_required=false, \DateTime $default_start=null, \DateTime $default_end=null)
    {
        $this->is_filter_time   = $is_filter_time;
        $this->is_required      = $is_required;
        $this->default_start    = $default_start ?: new \DateTime;
        $this->default_end      = $default_end ?: new \DateTime;
        parent::__construct([]);
    }

    /**
     * @return bool
     */
    public function isFilterTime()
    {
        return $this->is_filter_time;
    }

    /**
     * @return bool
     */
    public function isRequired()
    {
        return $this->is_required;
    }

    /**
     * @return \DateTime
     */
    public function getDefaultStart()
    {
        return $this->default_start;
    }

    /**
     * @return \DateTime
     */
    public function getDefaultEnd()
    {
        return $this->default_end;
    }
}