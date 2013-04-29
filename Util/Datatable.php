<?php

namespace Ali\DatatableBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\Query,
    Doctrine\ORM\Query\Expr\Join;
use Ali\DatatableBundle\Util\Factory\Query\QueryInterface,
    Ali\DatatableBundle\Util\Factory\Query\DoctrineBuilder,
    Ali\DatatableBundle\Util\Formatter\Renderer,
    Ali\DatatableBundle\Util\Factory\Prototype\PrototypeBuilder ;

class Datatable
{

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    /** @var \Ali\DatatableBundle\Util\Factory\Query\QueryInterface */
    protected $queryBuilder;
    
    /** @var boolean */
    protected $has_action = true;
 
    /** @var boolean */
    protected $has_renderer_action = false;
    
    /** @var array */
    protected $fixed_data = NULL;
    
    /** @var closure */
    protected $renderer = NULL;
    
    /** @var array */
    protected $renderers = NULL;
    
    /** @var Renderer */
    protected $renderer_obj = null;

    /** @var boolean */
    protected $search = FALSE;
    
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
        $this->queryBuilder = new DoctrineBuilder($container);
        self::$current_instance = $this;
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
        $this->queryBuilder->addJoin($join_field, $alias, $type, $cond);
        return $this;
    }

    /**
     * execute
     * 
     * @param int $hydration_mode
     * 
     * @return Response 
     */
    public function execute($hydration_mode = Query::HYDRATE_ARRAY)
    {
        $request = $this->request;
        $iTotalRecords = $this->queryBuilder->getTotalRecords();
        $data = $this->queryBuilder->getData($hydration_mode);
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
        
        if (!is_null($this->renderer_obj))
        {
            $this->renderer_obj->applyTo($data);
        }
        
        $output = array(
            "sEcho" => intval($request->get('sEcho')),
            "iTotalRecords" => $iTotalRecords,
            "iTotalDisplayRecords" => $iTotalRecords,
            "aaData" => $data
        );
        return new Response(json_encode($output));
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
     * get entity name
     * 
     * @return string
     */
    public function getEntityName()
    {
        return $this->queryBuilder->getEntityName();
    }

    /**
     * get entity alias
     * 
     * @return string
     */
    public function getEntityAlias()
    {
        return $this->queryBuilder->getEntityAlias();
    }

    /**
     * get fields
     * 
     * @return array
     */
    public function getFields()
    {
        return $this->queryBuilder->getFields();
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
     * retrun true if the actions column is overridden by twig renderer
     * 
     * @return boolean
     */
    public function getHasRendererAction()
    {
        return $this->has_renderer_action;
    }
        
    /**
     * get order field
     *
     * @return string
     */
    public function getOrderField()
    {
        return $this->queryBuilder->getOrderField();
    }

    /**
     * get order type
     * 
     * @return string
     */
    public function getOrderType()
    {
        return $this->queryBuilder->getOrderType();
    }

    /**
     * create raw prototype
     *
     * @param string $type
     * 
     * @return PrototypeBuilder 
     */
    public function getPrototype($type)
    {
        return new PrototypeBuilder($this->container,$type);
    }
    
    /**
     * get query builder
     * 
     * @return QueryInterface 
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * get search
     * 
     * @return boolean
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * Gets the search columns
     *
     * @return array the searchcolumns
     */
    public function getSearchColumns()
    {
        return $this->searchColumns;
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
        $this->queryBuilder->setEntity($entity_name, $entity_alias);
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
        $this->queryBuilder->setFields($fields);
        return $this;
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
    public function setOrder($order_field, $order_type)
    {
        $this->queryBuilder->setOrder($order_field, $order_type);
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
     * set query builder
     * 
     * @param QueryInterface $queryBuilder 
     */
    public function setQueryBuilder(QueryInterface $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
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
     * set renderers as twig views
     * 
     * @example: To override the actions column
     * 
     *      ->setFields(
     *          array(
     *             "field label 1" => 'x.field1',
     *             "field label 2" => 'x.field2',
     *             "_identifier_"  => 'x.id'
     *          )
     *      )
     *      ->setRenderers(
     *          array(
     *             2 => array(
     *               'view' => 'AliDatatableBundle:Renderers:_actions.html.twig',
     *               'params' => array(
     *                  'edit_route'    => 'matche_edit',
     *                  'delete_route'  => 'matche_delete',
     *                  'delete_form_prototype'   => $datatable->getPrototype('delete_form')
     *               ),
     *             ),
     *          )
     *       )
     * 
     * @param array $renderers
     * 
     * @return Datatable 
     */
    public function setRenderers(array $renderers)
    {
        $this->renderers = $renderers;
        if (!empty($this->renderers))
        {
            $this->renderer_obj = new Renderer($this->container, $this->renderers, $this->getFields());
        }
        $actions_index = array_search('_identifier_',array_keys($this->getFields()));
        if ( $actions_index != FALSE && isset($renderers[$actions_index]) )
        {
            $this->has_renderer_action = true;
        }
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
        $this->queryBuilder->setWhere($where, $params);
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
        $this->queryBuilder->setSearch($search);
        return $this;
    }

    /**
     * Set columns that should have search enabled
     *
     * @param array $searchColumns The searchable columns
     */
    public function setSearchColumns($searchColumns)
    {
        $this->searchColumns = $searchColumns;
        return $this;
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

}