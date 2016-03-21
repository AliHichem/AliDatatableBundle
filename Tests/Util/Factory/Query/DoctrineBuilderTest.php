<?php

namespace Ali\DatatableBundle\Tests\Util\Query;

use Ali\DatatableBundle\Tests\BaseTestCase;

class DoctrineBuilderTest extends BaseTestCase
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

    public function testAddJoin()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();
        $qb->addJoin('p.features', 'f');
        $this->assertAttributeEquals(array(0 => array(0 => "p.features", 1 => "f", 2 => "INNER", 3 => "")), 'joins', $qb);
    }

    public function testGetTotalRecords()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();
        $v  = $qb->getTotalRecords();
        $this->assertEquals('1', $v);
    }

    public function testGetData()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();
        $v  = $qb->getData();
        $this->assertInternalType('array', $v);
        $this->assertInternalType('array', $v[0]);
        $this->assertInternalType('array', $v[0][0]);
        $this->assertInstanceOf('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', $v[1][0]);
    }

    public function testGetEntityName()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();
        $this->assertEquals('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', $qb->getEntityName());
    }

    public function testGetEntityAlias()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();
        $this->assertEquals('p', $qb->getEntityAlias());
    }

    public function testGetFields()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();
        $this->assertEquals(array(
            "title"        => 'p.name',
            "_identifier_" => 'p.id'), $qb->getFields());
    }

    public function testGetOrderField()
    {
        $d  = $this->_datatable
                        ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                        ->setFields(
                                array(
                                    "title"        => 'p.name',
                                    "_identifier_" => 'p.id')
                        )->setOrder('p.id', 'asc');
        $qb = $d->getQueryBuilder();
        $this->assertEquals('p.id', $qb->getOrderField());
    }

    public function testGetOrderType()
    {
        $d  = $this->_datatable
                        ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                        ->setFields(
                                array(
                                    "title"        => 'p.name',
                                    "_identifier_" => 'p.id')
                        )->setOrder('p.id', 'asc');
        $qb = $d->getQueryBuilder();
        $this->assertEquals('asc', $qb->getOrderType());
    }

    public function testGetDoctrineQueryBuilder()
    {
        $d  = $this->_datatable
                        ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                        ->setFields(
                                array(
                                    "title"        => 'p.name',
                                    "_identifier_" => 'p.id')
                        )->setOrder('p.id', 'asc');
        $qb = $d->getQueryBuilder();
        $this->assertInstanceOf('Doctrine\ORM\QueryBuilder', $qb->getDoctrineQueryBuilder());
    }

    public function testSetEntity()
    {
        $d  = $this->_datatable
                        ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                        ->setFields(
                                array(
                                    "title"        => 'p.name',
                                    "_identifier_" => 'p.id')
                        )->setOrder('p.id', 'asc');
        $qb = $d->getQueryBuilder();
        $this->assertEquals('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', $qb->getEntityName());
        $this->assertEquals('p', $qb->getEntityAlias());
    }

    public function testSetFields()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();
        $this->assertEquals(array(
            "title"        => 'p.name',
            "_identifier_" => 'p.id'), $qb->getFields());
    }

    public function testSetOrder()
    {
        $d  = $this->_datatable
                        ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                        ->setFields(
                                array(
                                    "title"        => 'p.name',
                                    "_identifier_" => 'p.id')
                        )->setOrder('p.id', 'asc');
        $qb = $d->getQueryBuilder();
        $this->assertEquals('p.id', $qb->getOrderField());
        $this->assertEquals('asc', $qb->getOrderType());
    }

    public function testSetFixedData()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();
        $qb->setFixedData(array(array('HTC m8', 2)));
        $this->assertAttributeEquals(array(array('HTC m8', 2)), 'fixed_data', $qb);
    }

    public function testSetWhere()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
                )
                ->setWhere('p.id < 2');
        $qb = $d->getQueryBuilder();
        $v  = $qb->getData();
        $this->assertInternalType('array', $v);
        $this->assertInternalType('array', $v[0]);
        $this->assertInternalType('array', $v[0][0]);
        $this->assertInstanceOf('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', $v[1][0]);
    }

    public function testSetGroupBy()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
                )
                ->setGroupBy('p.id');
        $qb = $d->getQueryBuilder();
        $v  = $qb->getData();
        $this->assertInternalType('array', $v);
        $this->assertInternalType('array', $v[0]);
        $this->assertInternalType('array', $v[0][0]);
        $this->assertInstanceOf('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', $v[1][0]);
    }

    public function testSetSearch()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();
        $v  = $qb->getData();
        $this->assertAttributeEquals(FALSE, 'search', $qb);
        $qb->setSearch(TRUE);
        $this->assertAttributeEquals(TRUE, 'search', $qb);
    }

    public function testSetDoctrineQueryBuilder()
    {
        $d  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                array(
                    "title"        => 'p.name',
                    "_identifier_" => 'p.id')
        );
        $qb = $d->getQueryBuilder();

        $dqb = $this->_em->createQueryBuilder();
        $qb->setDoctrineQueryBuilder($dqb);
        $this->assertEquals($dqb, $qb->getDoctrineQueryBuilder());
    }

}
