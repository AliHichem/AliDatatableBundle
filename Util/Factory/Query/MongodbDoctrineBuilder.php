<?php

namespace Ali\DatatableBundle\Util\Factory\Query;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Query;

class MongodbDoctrineBuilder implements QueryInterface
{

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    /** @var \Doctrine\ODM\MongoDB\DocumentManager */
    protected $dm;

    /** @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    /** @var \Doctrine\ODM\MongoDB\QueryBuilder */
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
    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->dm           = $this->container->get('doctrine_mongodb')->getManager();
        $this->request      = $this->container->get('request');
        $this->queryBuilder = $this->dm->createQueryBuilder();
    }

    /**
     * get the search dql
     * 
     * @return string
     */
    protected function _addSearch(\Doctrine\ODM\MongoDB\Query\Builder $queryBuilder)
    {
        throw new \Exception('ODM search not implemented');
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
        throw new \Exception('ODM join not supported');
    }

    /**
     * get total records
     * 
     * @return integer 
     */
    public function getTotalRecords()
    {
        $qb = clone $this->queryBuilder;
        //$this->_addSearch($qb);
        if (empty($gb) || !in_array($this->fields['_identifier_'], $gb))
        {
            return $qb->count()->getQuery()->execute();
        }
    }

    /**
     * get data
     * 
     * @param int $hydration_mode
     * 
     * @return array 
     */
    public function getData($hydration_mode)
    {
        if ($hydration_mode !== Query::HYDRATE_ARRAY)
        {
            throw new \Exception(sprintf('Only array hydration mode is support for datatable'));
        }
        $request     = $this->request;
        $dql_fields  = array_values($this->fields);
        $order_field = null;
        if ($request->get('iSortCol_0') != null)
        {
            $order_field = $dql_fields[$request->get('iSortCol_0')];
        }
        $qb = clone $this->queryBuilder;
        if (!is_null($order_field))
        {
            $qb->sort($order_field, $request->get('sSortDir_0', 'asc'));
        }
        $selectFields = $this->fields;
        foreach ($selectFields as &$field)
        {
            if (preg_match('~as~', $field))
            {
                throw new \Exception(sprintf('cannot use "as" keyword with Mongodb driver'));
            }
        }
//        $qb->select($selectFields);
//        $this->_addSearch($qb);
//        $qb->hydrate(false);
        $limit = (int) $request->get('iDisplayLength');
        $skip  = (int) $request->get('iDisplayStart');
        if ($limit > 0)
        {
            $qb->skip($skip)->limit($limit);
        }
        $query                = $qb->getQuery();
        $items                = $query->execute()->toArray();
        $iTotalDisplayRecords = (string) count($items);
        $data                 = array();
        foreach ($items as $item)
        {
            $_item = [];
            $item  = $this->_toArray($item);
            foreach ($selectFields as $key => $value)
            {
                $_item[$value] = isset($item[$value]) ? $item[$value] : NULL;
            }
            $data[] = array_values($_item);
        }
        return [$data, $items];
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
     * @param type $entity_name
     * @param type $entity_alias
     * 
     * @return Datatable 
     */
    public function setEntity($entity_name, $entity_alias)
    {
        $this->entity_name  = $entity_name;
        $this->entity_alias = $entity_alias;
        $this->queryBuilder->find($entity_name);
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
        return $this;
    }

    /**
     * set order
     * 
     * @param type $order_field
     * @param type $order_type
     * 
     * @return Datatable 
     */
    public function setOrder($order_field, $order_type)
    {
        $this->order_field = strtolower($order_field);
        $this->order_type  = strtolower($order_type);
        return $this;
    }

    /**
     * set fixed data
     * 
     * @param type $data
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
        throw new \Exception('ODM where statement not supported');
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
        throw new \Exception('ODM groupby statement not supported');
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
