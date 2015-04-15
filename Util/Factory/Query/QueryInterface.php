<?php

namespace Ali\DatatableBundle\Util\Factory\Query;

interface QueryInterface
{
    
    const DQL_ALIAS_PATTERN = "/([A-z]*\.[A-z]+)?\sas\s(.*)$/";
    
    /**
     * get total records
     * 
     * @return integer 
     */
    function getTotalRecords();

    /**
     * get data
     * 
     * @param int $hydration_mode
     * 
     * @return array
     */
    function getData($hydration_mode);

    /**
     * set entity
     * 
     * @param string $entity_name
     * @param string $entity_alias
     * 
     * @return Datatable 
     */
    function setEntity($entity_name, $entity_alias);

    /**
     * set fields
     * 
     * @param array $fields
     * 
     * @return Datatable 
     */
    function setFields(array $fields);

    /**
     * get entity name
     * 
     * @return string
     */
    function getEntityName();

    /**
     * get entity alias
     * 
     * @return string
     */
    function getEntityAlias();

    /**
     * get fields
     * 
     * @return array
     */
    function getFields();

    /**
     * get order field
     *
     * @return string
     */
    function getOrderField();

    /**
     * get order type
     * 
     * @return string
     */
    function getOrderType();

    /**
     * set order
     * 
     * @param string $order_field
     * @param string $order_type
     * 
     * @return Datatable 
     */
    function setOrder($order_field, $order_type);

    /**
     * set fixed data
     * 
     * @param type $data
     * 
     * @return Datatable 
     */
    function setFixedData($data);

    /**
     * set query where
     * 
     * @param string $where
     * @param array  $params
     * 
     * @return Datatable 
     */
    function setWhere($where, array $params = array());

    /**
     * set search
     *
     * @param bool $search
     *
     * @return Datatable
     */
    function setSearch($search); 

    /**
     * add join
     * 
     * @example:
     *      ->setJoin( 
     *              'r.event', 
     *              'e', 
     *              \Doctrine\ORM\Query\Expr\Join::INNER_JOIN, 
     *              'e.name like %test%') 
     * 
     * @param string $join_field
     * @param string $alias
     * @param string $type
     * @param string $cond
     * 
     * @return Datatable 
     */
    function addJoin($join_field, $alias, $type = Join::INNER_JOIN, $cond = '');
    
    /**
     * set filtering type
     * 's' strict
     * 'f' full => LIKE '%' . $value . '%'
     * 'b' begin => LIKE '%' . $value
     * 'e' end => LIKE $value . '%'
     * 
     * @example 
     * 
     *      ->setFilteringType(array(0 => 's',2 => 'f',5 => 'b'))
     * 
     * @param array $filtering_type
     * 
     * @return Datatable
     */
    function setFilteringType(array $filtering_type);
    
    function getFilteringType();
}
