<?php

namespace Ali\DatatableBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query,
    Doctrine\ORM\Query\Expr\Join;

class Datatable
{

    protected $container;
    protected $request;
    /* @var Doctrine\ORM\EntityManager $em */
    protected $em;
    protected $entity_name;
    protected $entity_alias;
    protected $fields;
    protected $order_field = NULL;
    protected $order_type = "asc";
    protected $where = NULL;
    protected $joins = array();
    protected $has_action = true;
    protected $fixed_data = NULL;
    protected $renderer = NULL;
    protected static $instances = array();
    protected static $current_instance = NULL;

    /**
     * class constructor 
     * 
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->request = $this->container->get('request');
        self::$current_instance = $this;
    }

    /**
     * set datatable identifier
     * 
     * @param string $id
     * 
     * @return Datatable 
     */
    public function setDatatableId($id)
    {
        if (!array_key_exists($id, self::$instances))
        {
            self::$instances[$id] = $this;
        }
        else
        {
            throw new \Exception('Identifer already exists');
        }
        return $this;
    }

    /**
     * get datatable instance by id
     *  return current instance if null
     * 
     * @param string $id
     * 
     * @return Datatable .
     */
    public static function getInstance($id)
    {
        $instance = NULL;
        if (array_key_exists($id, self::$instances))
        {
            $instance = self::$instances[$id];
        }
        else
        {
            $instance = self::$current_instance;
        }

        if (is_null($instance))
        {
            throw new \Exception('No instance found for datatable, you should set a datatable id in your
            action with "setDatatableId" using the id from your view ');
        }

        return $instance;
    }

    /**
     * set entity
     * 
     * @param type $entity_name
     * @param type $entity_alias
     * 
     * @return Datatable 
     */
    public function setEntity($entity_name,
            $entity_alias)
    {
        $this->entity_name = $entity_name;
        $this->entity_alias = $entity_alias;
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
     * get has_action
     * 
     * @return boolean
     */
    public function getHasAction()
    {
        return $this->has_action;
    }

    /**
     * set has action
     * 
     * @param type $has_action
     * 
     * @return Datatable
     */
    public function setHasAction($has_action)
    {
        $this->has_action = $has_action;
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
    public function setOrder($order_field,
            $order_type)
    {
        $this->order_field = $order_field;
        $this->order_type = $order_type;
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
    public function setWhere($where,
            array $params = array())
    {
        $this->where = new \stdClass();
        $this->where->dql = $where;
        $this->where->params = $params;
        return $this;
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
        $dql_join = " {$type} join {$join_field} {$alias} ";
        if ($cond != '')
        {
            $dql_join .= " with {$cond} ";
        }
        $this->joins[] = $dql_join;
        return $this;
    }

    /**
     * get total records
     * 
     * @return integer 
     */
    protected function _getTotalRecords()
    {
        $query = $this->em
                ->createQuery("select count({$this->fields['_identifier_']}) 
                                        from {$this->entity_name} {$this->entity_alias}");
        return $query->getSingleScalarResult();
    }

    /**
     * get data
     * 
     * @return array
     */
    protected function _getData($hydration_mode)
    {
        $request = $this->request;
        $dql_fields = array_values($this->fields);
        $this->order_field = $dql_fields[$request->get('iSortCol_0')];

        $dql = "select ";
        if ($hydration_mode == Query::HYDRATE_ARRAY)
        {
            $dql .= implode(" , ", $this->fields) . " ";
        }
        else
        {
            $dql .= " {$this->entity_alias} ";
        }
        $dql .= " from {$this->entity_name} {$this->entity_alias} ";

        if (!empty($this->joins))
        {
            foreach ($this->joins as $join)
            {
                $dql .= $join;
            }
        }
        
        if ($this->where instanceof \stdClass && !is_null($this->where->dql))
        {
            $dql .= " where {$this->where->dql} ";
        }

        if (!is_null($this->order_field))
        {
            $dql .= " order by {$this->order_field} {$request->get('sSortDir_0', 'asc')} ";
        }

        $query = $this->em->createQuery($dql);
        /* @var $query Query */
        if ($this->where instanceof \stdClass && !empty($this->where->params))
        {
            $query->setParameters($this->where->params);
        }
        $iDisplayLength = (int) $request->get('iDisplayLength');
        if ($iDisplayLength > 0)
        {
            $query->setMaxResults($iDisplayLength)->setFirstResult($request->get('iDisplayStart'));
        }
        $items = $query->getResult($hydration_mode);
        $iTotalDisplayRecords = (string) count($items);
        $data = array();
        if ($hydration_mode == Query::HYDRATE_ARRAY)
        {
            foreach ($items as $item)
            {
                $data[] = array_values($item);
            }
        }
        else
        {
            foreach ($items as $item)
            {
                $_data = array();
                foreach ($this->fields as $field)
                {
                    $method = "get" . ucfirst(substr($field, strpos($field, '.') + 1));
                    $_data[] = $item->$method();
                }
                $data[] = $_data;
            }
        }
        return $data;
    }

    /**
     * set a php closure as renderer
     * 
     * @example:
     * 
     *  $controller_instance = $this;
     *  $datatable = $this->get('datatable')
     *       ->setEntity("AliBaseBundle:Entity", "e")
     *       ->setFields($fields)
     *       ->setOrder("e.created", "desc")
     *       ->setRenderer(
     *               function(&$data) use ($controller_instance)
     *               {
     *                   foreach ($data as $key => $value)
     *                   {
     *                       if ($key == 1)
     *                       {
     *                           $data[$key] = $controller_instance
     *                               ->get('templating')
     *                               ->render('AliBaseBundle:Entity:_decorator.html.twig',
     *                                       array(
     *                                           'data' => $value
     *                                       )
     *                               );
     *                       }
     *                   }
     *               }
     *         )
     *       ->setHasAction(true);
     * 
     * @param \Closure $renderer
     * 
     * @return Datatable 
     */
    public function setRenderer(\Closure $renderer)
    {
        $this->renderer = $renderer;
        return $this;
    }

    /**
     * execute
     *
     * @return Response 
     */
    public function execute($hydration_mode = Query::HYDRATE_ARRAY)
    {
        $request = $this->request;
        $iTotalRecords = $this->_getTotalRecords();
        $data = $this->_getData($hydration_mode);
        if (!is_null($this->fixed_data))
        {
            $this->fixed_data = array_reverse($this->fixed_data);
            foreach ($this->fixed_data as $item)
            {
                array_unshift($data, $item);
            }
        }
        if (!is_null($this->renderer))
        {
            array_walk($data, $this->renderer);
        }
        $output = array(
            "sEcho" => intval($request->get('sEcho')),
            "iTotalRecords" => $iTotalRecords,
            "iTotalDisplayRecords" => $iTotalRecords,
            "aaData" => $data
        );
        return new Response(json_encode($output));
    }

}