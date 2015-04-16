<?php

namespace Ali\DatatableBundle\Util\Factory\Query;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\DependencyInjection\ContainerInterface;

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

    /** @var array */
    protected $filtering_type = array();
    
    /**
     * class constructor 
     * 
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container    = $container;
        $this->em           = $this->container->get('doctrine.orm.entity_manager');
        $this->request      = $this->container->get('request');
        $this->queryBuilder = $this->em->createQueryBuilder();
    }

    /**
     * get the search dql
     * 
     * @return string
     */
    protected function _addSearch(\Doctrine\ORM\QueryBuilder $queryBuilder)
    {

        if ($this->search !== true) {
            return;
        }
        
        $request = $this->request;
        $search_fields = array_values($this->fields);
        $global_search = $request->query->get('sSearch');
        $orExpr = $queryBuilder->expr()->orX();
        $filteringType = $this->getFilteringType();
        foreach ($search_fields as $i => $search_field) {
            $search_field = $this->getSearchField($search_field);

            // Global filtering
            if (!empty($global_search) || $global_search == '0') {

                if ($request->query->get('bSearchable_' . $i) && $request->query->get('bSearchable_' . $i) == "true") {
                    $qbParam = "sSearch_global_" . $i;

                    if ($this->isStringDQLQuery($search_field)) {
                        $orExpr->add(
                                $queryBuilder->expr()->eq($search_field, ':' . $qbParam)
                        );
                        $queryBuilder->setParameter($qbParam, $global_search);
                    } else {
                        $orExpr->add($queryBuilder->expr()->like(
                                        $search_field, ":" . $qbParam
                        ));
                        $queryBuilder->setParameter($qbParam, "%" . $global_search . "%");
                    }
                }
            }

            // Individual filtering
            $searchName = "sSearch_" . $i;
            $search_param = $request->get($searchName);
            $bRegex = $request->get("bRegex_{$i}");
            if ($request->get("bSearchable_{$i}") != 'false' && (!empty($search_param) || $search_param == '0')) {
                $queryBuilder->andWhere($queryBuilder->expr()->like($search_field, ":" . $searchName));

                if (array_key_exists($i, $filteringType)) {
                    switch ($filteringType[$i]) {
                        case 's':
                            $queryBuilder->setParameter($searchName, $request->get($searchName));
                            break;
                        case 'f':
                            $queryBuilder->setParameter($searchName, sprintf("%%%s%%", $request->get($searchName)));
                            break;
                        case 'b':
                            $queryBuilder->setParameter($searchName, sprintf("%%%s", $request->get($searchName)));
                            break;
                        case 'e':
                            $queryBuilder->setParameter($searchName, sprintf("%s%%", $request->get($searchName)));
                            break;
                    }
                } else {
                    $queryBuilder->setParameter($searchName, sprintf("%%%s%%", $request->get($searchName)));
                }
            }
        }

        if (!empty($global_search) || $global_search == '0') {
            $queryBuilder->andWhere($orExpr);
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
//        $this->_addSearch($qb);
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
     * get total records after filtering
     * 
     * @return integer 
     */
    public function getTotalDisplayRecords()
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
     * @param int $hydration_mode
     * 
     * @return array 
     */
    public function getData($hydration_mode)
    {
        $request    = $this->request;
        $dql_fields = array_values($this->fields);

        // add sorting
        if ($request->get('iSortCol_0') !== null)
        {
            $order_field = explode(' as ', $dql_fields[$request->get('iSortCol_0')]);
            end($order_field);
            $order_field = current($order_field);
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
        
        foreach ($this->fields as $key => $field) {
            if (stripos($field, " as ") !== false || stripos($field, "(") !== false) {
                $select[] = $field;
            }
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
        
        $objects = $query->getResult(Query::HYDRATE_OBJECT);
        $maps    = $query->getResult(Query::HYDRATE_SCALAR);
        $data    = array();
        
        $aliasPattern = self::DQL_ALIAS_PATTERN;
        
        $get_scalar_key = function($field) use($aliasPattern) {

            $has_alias = preg_match_all($aliasPattern, $field, $matches);
            $_f        = ( $has_alias == true ) ? $matches[2][0] : $field;
            $_f        = str_replace('.', '_', $_f);

            return $_f;
        };
        
        $fields = array();
        
        foreach ($this->fields as $field)
        {
            $fields[] = $get_scalar_key($field);
        }

        foreach ($maps as $map)
        {
            $item = array();
            foreach ($fields as $_field)
            {
                $item[] = $map[$_field];
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
     * @param type $entity_name
     * @param type $entity_alias
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
     * @param type $order_field
     * @param type $order_type
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
     * @return DoctrineBuilder
     */
    public function setFilteringType(array $filtering_type)
    {
        $this->filtering_type = $filtering_type;
        return $this;
    }
    
    public function getFilteringType() {
        return $this->filtering_type;
    }

    /**
     * The most of time $search_field is a string that represent the name of a field in data base.
     * But some times, $search_field is a DQL subquery
     * 
     * @param string $field
     * @return string
     */
    private function getSearchField($field)
    {   
        if($this->isStringDQLQuery($field)) {
            
            $dqlQuery = $field;            
            
            $lexer = new Query\Lexer($field);
            
            // We have to rename some identifier or the execution will crash
            while($lexer->moveNext() == true) {
                if($this->isTheIdentifierILookingFor($lexer)) {
                    $replacement = sprintf("$1%s_%d$3", $lexer->lookahead['value'], mt_rand());
                    $pattern = sprintf("/([\(\s])(%s)([\s\.])/", $lexer->lookahead['value']);
                    
                    $dqlQuery = preg_replace($pattern, $replacement, $dqlQuery);
                }
            }
            
            $dqlQuery = substr($dqlQuery, 0, strripos($dqlQuery, ")") + 1);
            
            return $dqlQuery;
        }

        
        $field = explode(' ', trim($field));
        return $field[0];
    }
    
    private function isTheIdentifierILookingFor(Query\Lexer $lexer)
    {
        if($lexer->token['type'] === Query\Lexer::T_IDENTIFIER && $lexer->isNextToken(Query\Lexer::T_IDENTIFIER)) {
            return true;
        }
        
        if($lexer->token['type'] === Query\Lexer::T_IDENTIFIER && $lexer->isNextToken(Query\Lexer::T_AS)) {
            
            $lexer->moveNext();

            if($lexer->lookahead['type'] === Query\Lexer::T_IDENTIFIER) {
                return true;
            }
        }
        
        return false;
    }

    private function isStringDQLQuery($value)
    {
         $keysWord = array(
            "SELECT ",
            " FROM ",
            " WHERE "
        );
        
        foreach($keysWord as $keyWord) {
            if(stripos($value, $keyWord) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
}
