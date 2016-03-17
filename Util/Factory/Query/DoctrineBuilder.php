<?php

namespace Ali\DatatableBundle\Util\Factory\Query;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class DoctrineBuilder implements QueryInterface
{

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    /** @var \Doctrine\ORM\QueryBuilder */
    protected $queryBuilder;

    /** @var string */
    protected $entity_name;

    /** @var string */
    protected $entity_alias;

    /** @var array */
    protected $fields;

    /** @var string */
    protected $order_field = NULL;

    /** @var string */
    protected $order_type = "asc";

    /** @var string */
    protected $where = NULL;

    /** @var array */
    protected $joins = array();

    /** @var boolean */
    protected $has_action = true;

    /** @var array */
    protected $fixed_data = NULL;

    /** @var closure */
    protected $renderer = NULL;

    /** @var boolean */
    protected $search = FALSE;

    /**
     * class constructor 
     * 
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container, $em)
    {
        $this->container    = $container;
        $this->em           = $em;
        $this->request      = Request::createFromGlobals();
        $this->queryBuilder = $this->em->createQueryBuilder();
    }

    /**
     * get the search dql
     * 
     * @return string
     */
    protected function _addSearch(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        if ($this->search == TRUE)
        {
            $request       = $this->request;
            $search_fields = array_values($this->fields);
            foreach ($search_fields as $i => $search_field)
            {
                $search_param = $request->get("sSearch_{$i}");
                if ($search_param !== false && $search_param != '')
                {
                    $field        = explode(' ', trim($search_field));
                    $search_field = $field[0];

                    $queryBuilder->andWhere(" $search_field like :ssearch{$i} ");
                    $queryBuilder->setParameter("ssearch{$i}", '%' . $request->get("sSearch_{$i}") . '%');
                }
            }
        }
    }

    /**
     * convert object to array
     * @param object $object
     * @return array
     */
    protected function _toArray($object)
    {
        $reflectionClass = new \ReflectionClass(get_class($object));
        $array           = array();
        foreach ($reflectionClass->getProperties() as $property)
        {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($object);
            $property->setAccessible(false);
        }
        return $array;
    }

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
    public function addJoin($join_field, $alias, $type = Join::INNER_JOIN, $cond = '')
    {
        if ($cond != '')
        {
            $cond = " with {$cond} ";
        }
        $join_method   = $type == Join::INNER_JOIN ? "innerJoin" : "leftJoin";
        $this->queryBuilder->$join_method($join_field, $alias, null, $cond);
        $this->joins[] = array($join_field, $alias, $type, $cond);
        return $this;
    }

    /**
     * get total records
     * 
     * @return integer 
     */
    public function getTotalRecords()
    {
        $qb = clone $this->queryBuilder;
        $this->_addSearch($qb);
        $qb->resetDQLPart('orderBy');

        $gb = $qb->getDQLPart('groupBy');
        if (empty($gb) || !in_array($this->fields['_identifier_'], $gb))
        {
            $qb->select(" count({$this->fields['_identifier_']}) ");
            return $qb->getQuery()->getSingleScalarResult();
        }
        else
        {
            $qb->resetDQLPart('groupBy');
            $qb->select(" count(distinct {$this->fields['_identifier_']}) ");
            return $qb->getQuery()->getSingleScalarResult();
        }
    }

    /**
     * get data
     * 
     * @return array 
     */
    public function getData()
    {
        $request    = $this->request;
        $dql_fields = array_values($this->fields);

        // add sorting
        if ($request->get('iSortCol_0') !== null)
        {
            $order_field = current(explode(' as ', $dql_fields[$request->get('iSortCol_0')]));
        }
        else
        {
            $order_field = null;
        }
        $qb = clone $this->queryBuilder;
        if (!is_null($order_field))
        {
            $qb->orderBy($order_field, $request->get('sSortDir_0', 'asc'));
        }
        else
        {
            $qb->resetDQLPart('orderBy');
        }

        // extract alias selectors
        $select = array($this->entity_alias);
        foreach ($this->joins as $join)
        {
            $select[] = $join[1];
        }
        $qb->select(implode(',', $select));

        // add search
        $this->_addSearch($qb);

        // get results and process data formatting
        $query          = $qb->getQuery();
        $iDisplayLength = (int) $request->get('iDisplayLength');
        if ($iDisplayLength > 0)
        {
            $query->setMaxResults($iDisplayLength)->setFirstResult($request->get('iDisplayStart'));
        }
        $objects         = $query->getResult(Query::HYDRATE_OBJECT);
        $data            = array();
        $entity_alias    = $this->entity_alias;
        $joins           = $this->joins;
        $__getParentChain = function($field) use($entity_alias, $joins, &$__getParentChain) {
            foreach ($joins as $join)
            {
                if ($join[1] == $field[0])
                {
                    if ($join[0][0] == $entity_alias)
                    {
                        return substr($join[0], 2);
                    }
                    else
                    {
                        $f = $join[0];
                        if (strpos($f, ' '))
                        {
                            $_f = substr($f, 2, strpos($f, ' '));
                        }
                        else
                        {

                            $_f = substr($f, 2);
                        }
                        return $__getParentChain($join[0]) . '.' . $_f;
                    }
                }
            }
        };
        $__getKey = function($field) use($entity_alias, $__getParentChain) {
            $has_alias = preg_match_all('~([A-z]?\.[A-z]+)?\sas~', $field, $matches);
            $_f        = ( $has_alias > 0 ) ? $matches[1][0] : $field;
            $_f        = explode('.', $_f)[1];
            if ($field[0] != $entity_alias)
            {
                return $__getParentChain($field) . '.' . $_f;
            }
            return $_f;
        };
        $fields = array();
        foreach ($this->fields as $field)
        {
            $fields[] = $__getKey($field);
        }
        $__getValue = function($prop, $object)use(&$__getValue) {
            if (strpos($prop, '.'))
            {
                $_prop     = substr($prop, 0, strpos($prop, '.'));
                $ref_class = new \ReflectionClass($object);
                $property  = $ref_class->getProperty($_prop);
                $property->setAccessible(true);
                return $__getValue(substr($prop, strpos($prop, '.') + 1), $property->getValue($object));
            }
            $ref_class = new \ReflectionClass($object);
            $property  = $ref_class->getProperty($prop);
            $property->setAccessible(true);
            return $property->getValue($object);
        };
        foreach ($objects as $object)
        {
            $item = array();
            foreach ($fields as $_field)
            {
                $item[] = $__getValue($_field, $object);
            }
            $data[] = $item;
        }
        return array($data, $objects);
    }

    /**
     * get entity name
     * 
     * @return string
     */
    public function getEntityName()
    {
        return $this->entity_name;
    }

    /**
     * get entity alias
     * 
     * @return string
     */
    public function getEntityAlias()
    {
        return $this->entity_alias;
    }

    /**
     * get fields
     * 
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * get order field
     *
     * @return string
     */
    public function getOrderField()
    {
        return $this->order_field;
    }

    /**
     * get order type
     * 
     * @return string
     */
    public function getOrderType()
    {
        return $this->order_type;
    }

    /**
     * get doctrine query builder
     * 
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getDoctrineQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * set entity
     * 
     * @param string $entity_name
     * @param string $entity_alias
     * 
     * @return Datatable 
     */
    public function setEntity($entity_name, $entity_alias)
    {
        $this->entity_name  = $entity_name;
        $this->entity_alias = $entity_alias;
        $this->queryBuilder->from($entity_name, $entity_alias);
        return $this;
    }

    /**
     * set fields
     * 
     * @param array $fields
     * 
     * @return Datatable 
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        $this->queryBuilder->select(implode(', ', $fields));
        return $this;
    }

    /**
     * set order
     * 
     * @param string $order_field
     * @param string $order_type
     * 
     * @return Datatable 
     */
    public function setOrder($order_field, $order_type)
    {
        $this->order_field = $order_field;
        $this->order_type  = $order_type;
        $this->queryBuilder->orderBy($order_field, $order_type);
        return $this;
    }

    /**
     * set fixed data
     * 
     * @param array|null $data
     * 
     * @return Datatable 
     */
    public function setFixedData($data)
    {
        $this->fixed_data = $data;
        return $this;
    }

    /**
     * set query where
     * 
     * @param string $where
     * @param array  $params
     * 
     * @return Datatable 
     */
    public function setWhere($where, array $params = array())
    {
        $this->queryBuilder->where($where);
        $this->queryBuilder->setParameters($params);
        return $this;
    }

    /**
     * set query group
     * 
     * @param string $group
     * 
     * @return Datatable 
     */
    public function setGroupBy($group)
    {
        $this->queryBuilder->groupBy($group);
        return $this;
    }

    /**
     * set search
     * 
     * @param bool $search
     * 
     * @return Datatable
     */
    public function setSearch($search)
    {
        $this->search = $search;
        return $this;
    }

    /**
     * set doctrine query builder
     * 
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * 
     * @return DoctrineBuilder 
     */
    public function setDoctrineQueryBuilder(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

}
