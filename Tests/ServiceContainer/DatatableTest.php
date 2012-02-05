<?php

namespace Ali\DatatableBundle\Tests\ServiceContainer;

class DatatableTest extends BaseTestCase
{

    /** @var Ali\DatatableBundle\Util\Datatable */
    protected $_datatable;

    protected function __setUp()
    {
        $client = parent::createClient();
        $crawler = $client->request('GET', '/');
        $this->_container->set('request', $client->getRequest());
        $this->_datatable = $this->_container->get('datatable');
    }

    public function test_chainingClassBehavior()
    {
        $this->__setUp();
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setEntity('$entity_name', '$entity_alias'));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setFields(array()));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setFixedData('$data'));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setHasAction(TRUE));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setOrder('$order_field', '$order_type'));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setRenderer(function($value, $key)
                        {
                            return true;
                        }));
    }

}