<?php

namespace Ali\DatatableBundle\Tests\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    /**
     * set datatable configs
     * 
     * @return \Ali\DatatableBundle\Util\Datatable
     */
    private function _datatable()
    {
        return $this->get('datatable')
                        ->setEntity("Ali\DatatableBundle\Tests\TestBundle\Entity\Product", "p")
                        ->setFields(
                                array(
                                    "title"        => 'p.name',
                                    "description"  => 'p.description',
                                    "_identifier_" => 'p.id')
                        )
                        ->setWhere(
                                'p.id > :id', array('id' => 0)
                        )
                        ->setOrder("p.id", "desc")
                        ->setHasAction(true);
    }

    /**
     * Grid action
     * @return Response
     */
    public function gridAction()
    {
        return $this->_datatable()->execute();
    }

    public function indexAction()
    {
        $this->_datatable();
        return $this->render('TestBundle:Default:index.html.twig');
    }

    public function editAction($id)
    {
        return new Response('test');
    }

    public function deleteAction($id)
    {
        return new Response('test');
    }

}
