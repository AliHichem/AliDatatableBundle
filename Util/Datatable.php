<?php

namespace Ali\DatatableBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class Datatable
{
    private $container;
    private $request;
    /* @var Doctrine\ORM\EntityManager $em */
    private $em;
    private $entity_name;
    private $entity_alias;
    private $fields;
    private $order_field = NULL;
    private $order_type = "asc";
    private $has_action = true; 
    private $fixed_data = NULL;
    
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
    public function setOrder($order_field, $order_type)
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
     * get total records
     * 
     * @return integer 
     */
    private function _getTotalRecords()
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
    private function _getData()
    {
        $request = $this->request;
        $dql = "select ";
        $dql .= implode(" , ", $this->fields)." ";
        $dql .= " from {$this->entity_name} {$this->entity_alias} ";
        if (!is_null($this->order_field))
        {
            $dql .= " order by {$this->order_field} {$this->order_type} ";
        }
        $query = $this->em->createQuery($dql);
        $iDisplayLength = (int)$request->get('iDisplayLength');
        if ($iDisplayLength > 0)
        {
            $query->setMaxResults( $iDisplayLength )->setFirstResult( $request->get('iDisplayStart') );
        }
        $items = $query->getArrayResult();
        $iTotalDisplayRecords = (string)count($items);
        $data = array();
        foreach ($items as $item) 
        {
            $data[]= array_values($item);
        }
        return $data;
    }

    /**
     * execute
     *
     * @return Response 
     */
    public function execute()
    {
        $request = $this->request;
        $iTotalRecords = $this->_getTotalRecords();
        $data = $this->_getData();
        if(!is_null($this->fixed_data))
        {
            $this->fixed_data = array_reverse($this->fixed_data);
            foreach ($this->fixed_data as $item)
            {
                array_unshift($data, $item);
            }
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