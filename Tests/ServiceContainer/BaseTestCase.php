<?php

namespace Ali\DatatableBundle\Tests\ServiceContainer;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTestCase extends WebTestCase
{

    protected $_em;
    /** @var ContainerInterface */
    protected $_container;

    protected function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->_em = $kernel->getContainer()
                ->get('doctrine.orm.entity_manager');
        $this->_container = $kernel->getContainer();
    }

}