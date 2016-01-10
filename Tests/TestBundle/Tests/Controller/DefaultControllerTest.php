<?php

namespace Ali\DatatableBundle\Tests\Util;

use Ali\DatatableBundle\Tests\BaseTestCase;
use Ali\DatatableBundle\Util\Datatable;

class DefaultControllerTest extends BaseTestCase
{

    /** @var \Ali\DatatableBundle\Util\Datatable */
    protected $_datatable;

    /** @var \Symfony\Bundle\FrameworkBundle\Client */
    protected static $client;

    /** @var array */
    protected static $params;

    /** @var array */
    protected static $server;

    /**
     * {@inheritdoc}
     */
    protected function setUp($env = 'test')
    {
        parent::setUp($env);
        $client           = parent::createClient();
        $client->request('GET', '/');
        $this->_container->set('request', $client->getRequest());
        $this->_datatable = $this->_container->get('datatable');
    }

    public function testIndex()
    {
        $client  = parent::createClient();
        $r       = $this->_container->get('router')->generate('alidatatable_test_homepage');
        $crawler = $client->request('GET', $r);
        $res     = $client->getResponse();
        $this->assertContains(
                "ali-dta_", $res->getContent()
        );
    }

}
