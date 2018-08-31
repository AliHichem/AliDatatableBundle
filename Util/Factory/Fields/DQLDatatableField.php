<?php

namespace Ali\DatatableBundle\Util\Factory\Fields;

/**
 * way to use DQL as a field, ex.
 * CONCAT_WS(' ', v.type, '|', CONCAT(v.amount, '%'))
 * or
 * COUNT(l.id)
 *
 * Class DQLDatatableField
 * @package Ali\DatatableBundle\Util\Factory\Fields
 */
class DQLDatatableField extends DatatableField
{
    /**
     * DQL functions resulting in a string|stringifiable output, ex.
     * CONCAT_WS(' ', v.type, '|', CONCAT(v.amount, '%'))
     * COUNT(l.id)
     *
     * @var string
     */
    protected $dql;

    /**
     * Alias, as you'd normally use it in AS.
     * Please make sure you don't use odd characters SQL won't understand
     *
     * @var string
     */
    protected $alias;

    /**
     * certain queries like COUNT and AVG need to use HAVING, otherwise you'll get an
     * error "Invalid use of group functions"
     *
     * @var bool
     */
    protected $needs_having;

    /**
     * DQLDatatableField constructor.
     *
     * @param $dql
     * @param $alias
     * @param bool $needs_having
     */
    public function __construct($dql, $alias, $needs_having=false)
    {
        $this->dql = (string)$dql;
        $this->alias = (string)$alias;
        $this->needs_having = (bool)$needs_having;

        parent::__construct($dql);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @return bool
     */
    public function getNeedsHaving()
    {
        return $this->needs_having;
    }
}