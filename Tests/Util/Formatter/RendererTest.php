<?php

namespace Ali\DatatableBundle\Tests\Util\Formatter;

use Ali\DatatableBundle\Tests\BaseTestCase;
use Ali\DatatableBundle\Util\Formatter\Renderer;

class RendererTest extends BaseTestCase
{

    /** @var \Ali\DatatableBundle\Util\Datatable */
    protected $_datatable;

    /**
     * {@inheritdoc}
     */
    protected function setUp($env = 'test')
    {
        parent::setUp($env);
        $client           = parent::createClient();
        $crawler          = $client->request('GET', '/');
        $this->_container->set('request', $client->getRequest());
        $this->_datatable = $this->_container->get('datatable');
    }

    public function testApplyView()
    {
        $fields = array(
            "title"        => 'p.name',
            "_identifier_" => 'p.id')
        ;
        $r      = new Renderer($this->_container, array(), $fields);
        $out    = $r->applyView('AliDatatableBundle:Renderers:_default.html.twig', $fields);
        $this->assertInternalType('string', $out);
    }

    public function testApplyTo()
    {
        $fields = array(
            "title"        => 'p.name',
            "_identifier_" => 'p.id')
        ;
        $r      = new Renderer($this->_container, array(
            1 => array(
                'view'   => 'AliDatatableBundle:Renderers:_actions.html.twig',
                'params' => array(
                    'edit_route'            => 'alidatatable_test_edit',
                    'delete_route'          => 'alidatatable_test_delete',
                    'delete_form_prototype' => $this->_datatable->getPrototype('delete_form')
                ),
            ),
                ), $fields);
        $data   = array(array('something', 'eee'));
        $r->applyTo($data, array((object) array()));
        $this->assertContains('form', $data[0][1]);
    }

}
