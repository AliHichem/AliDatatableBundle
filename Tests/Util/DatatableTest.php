<?php

namespace Ali\DatatableBundle\Tests\Util;

use Ali\DatatableBundle\Tests\BaseTestCase;
use Ali\DatatableBundle\Util\Datatable;

class DatatableTest extends BaseTestCase
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

    public function testChainingClassBehavior()
    {
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setEntity('$entity_name', '$entity_alias'));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setFields(array()));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setFixedData('$data'));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setHasAction(TRUE));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setOrder('$order_field', '$order_type'));
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $this->_datatable->setRenderer(function($value, $key) {
                    return true;
                }));
    }

    public function testAddJoin()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Feature', 'f')
                ->setFields(
                        array(
                            "Category"     => 'c.name',
                            "Product"      => 'p.name',
                            "Feature"      => 'f.name',
                            "_identifier_" => 'f.id')
                )
                ->addJoin('f.product', 'p')
                ->addJoin('p.category', 'c');
        $r    = $this->_datatable->execute();
        /* @var $r \Symfony\Component\HttpFoundation\JsonResponse */
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $data = json_decode($r->getContent(), true);
        $this->assertArrayHasKey('sEcho', $data);
        $this->assertArrayHasKey('iTotalRecords', $data);
        $this->assertArrayHasKey('iTotalDisplayRecords', $data);
        $this->assertArrayHasKey('aaData', $data);
        $this->assertEquals(0, $data['sEcho']);
        $this->assertEquals('3', $data['iTotalRecords']);
        $this->assertEquals('3', $data['iTotalDisplayRecords']);
        $this->assertEquals(array(
            array('CatA','Laptop', 'CPU I7 Generation', 1),
            array('CatA','Laptop', 'SolidState drive', 2),
            array('CatA','Laptop', 'SLI graphic card ', 3),
                ), $data['aaData']);
    }

    public function testExecute()
    {
        $r    = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
                )
                ->execute();
        /* @var $r \Symfony\Component\HttpFoundation\JsonResponse */
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $data = json_decode($r->getContent(), true);
        $this->assertArrayHasKey('sEcho', $data);
        $this->assertArrayHasKey('iTotalRecords', $data);
        $this->assertArrayHasKey('iTotalDisplayRecords', $data);
        $this->assertArrayHasKey('aaData', $data);
        $this->assertEquals(0, $data['sEcho']);
        $this->assertEquals('1', $data['iTotalRecords']);
        $this->assertEquals('1', $data['iTotalDisplayRecords']);
        $this->assertEquals(array(array('Laptop', 1)), $data['aaData']);
    }

    public function testGetInstance()
    {
        $this->_datatable
                ->setDatatableId('test')
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $i = $this->_datatable->getInstance('test');
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $i);
        $this->assertEquals('p', $i->getEntityAlias());
    }

    public function testGetEntityName()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertEquals('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', $this->_datatable->getEntityName());
    }

    public function testGetEntityAlias()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertEquals('p', $this->_datatable->getEntityAlias());
    }

    public function testGetFields()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertInternalType('array', $this->_datatable->getFields());
    }

    public function testGetHasAction()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertInternalType('boolean', $this->_datatable->getHasAction());
    }

    public function testGetHasRendererAction()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertInternalType('boolean', $this->_datatable->getHasRendererAction());
    }

    public function testGetOrderField()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInternalType('string', $this->_datatable->getOrderField());
    }

    public function testGetOrderType()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInternalType('string', $this->_datatable->getOrderType());
    }

    public function testGetPrototype()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInstanceOf('Ali\DatatableBundle\Util\Factory\Prototype\PrototypeBuilder', $this->_datatable->getPrototype('delete_form'));
    }

    public function testGetQueryBuilder()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInstanceOf('Ali\DatatableBundle\Util\Factory\Query\DoctrineBuilder', $this->_datatable->getQueryBuilder());
    }

    public function testGetSearch()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInternalType('boolean', $this->_datatable->getSearch());
    }

    public function testSetEntity()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
        ;
        $this->assertEquals('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', $this->_datatable->getEntityName());
        $this->assertEquals('p', $this->_datatable->getEntityAlias());
    }

    public function testSetEntityManager()
    {
        $this->_datatable
                ->setEntityManager($this->_em)
        ;
        $class = get_class($this->_em);
        $qb    = $this->_datatable->getQueryBuilder()->getDoctrineQueryBuilder();
        $this->assertInstanceOf($class, $qb->getEntityManager());
    }

    public function testSetFields()
    {
        $fields = array(
            "title"        => 'p.name',
            "_identifier_" => 'p.id'
        );
        $this->_datatable->setFields($fields);
        $this->assertEquals($fields, $this->_datatable->getFields());
    }

    public function testAddField()
    {
        $fields = array(
            "title"        => 'p.name',
            "_identifier_" => 'p.id'
        );
        $this->_datatable->setFields($fields);
        $this->_datatable->addField('description', 'p.description');
        $this->assertEquals($fields + array('description' => 'p.description'), $this->_datatable->getFields());
    }

    public function testAddFields()
    {
        $fields = array(
            "title"        => 'p.name',
            "_identifier_" => 'p.id'
        );
        $this->_datatable->setFields($fields);
        $this->_datatable->addFields(array('description' => 'p.description'));
        $this->assertEquals($fields + array('description' => 'p.description'), $this->_datatable->getFields());
    }

    public function testSetHasAction()
    {
        $this->assertEquals(TRUE, $this->_datatable->getHasAction());
    }

    public function testSetOrder()
    {
        $this->_datatable->setOrder('p.id', 'asc');
        $this->assertEquals('p.id', $this->_datatable->getOrderField());
        $this->assertEquals('asc', $this->_datatable->getOrderType());
    }

    public function testSetFixedData()
    {
        $r    = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
                )
                ->setFixedData(array(array('HTC m8', 2)))
                ->execute();
        /* @var $r \Symfony\Component\HttpFoundation\JsonResponse */
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $r);
        $data = json_decode($r->getContent(), true);
        $this->assertEquals(array(array('HTC m8', 2), array('Laptop', 1)), $data['aaData']);
    }

    public function testSetQueryBuilder()
    {
        $qb = $this->_datatable->getQueryBuilder();
        $this->_datatable->setQueryBuilder($qb);
        $this->assertInstanceOf('Ali\DatatableBundle\Util\Factory\Query\QueryInterface', $this->_datatable->getQueryBuilder());
    }

    public function testSetWhere()
    {
        $r    = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
                )
                ->setWhere('p.id < 2')
                ->execute();
        $data = json_decode($r->getContent(), true);
        $this->assertEquals(array(array('Laptop', 1)), $data['aaData']);
    }

    public function testSetGroupBy()
    {
        $r    = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
                )
                ->setWhere('p.id < 2')
                ->setGroupBy('p.id')
                ->execute();
        $data = json_decode($r->getContent(), true);
        $this->assertEquals(array(array('Laptop', 1)), $data['aaData']);
    }

    public function testSetSearch()
    {
        $r    = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
                )
                ->setWhere('p.id < 2')
                ->setSearch(TRUE)
                ->execute();
        $data = json_decode($r->getContent(), true);
        $this->assertEquals(array(array('Laptop', 1)), $data['aaData']);
    }

    public function testSetDatatableId()
    {
        $dta1 = $this->_container->get('datatable');
        $dta1->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "description"  => 'p.description',
                            "_identifier_" => 'p.id')
                )
                ->setDatatableId('dta1');
        $dta2 = $this->_container->get('datatable');
        $dta2->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
                )
                ->setDatatableId("dta2");
        $this->assertNotEquals(Datatable::getInstance('dta1'), Datatable::getInstance('dta2'));
        $this->assertNotEquals(Datatable::getInstance('dta1')->getFields(), Datatable::getInstance('dta2')->getFields());
    }

    public function testGetMultiple()
    {
        $this->_datatable->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "description"  => 'p.description',
                            "_identifier_" => 'p.id')
        );
        $this->assertEquals(NULL, $this->_datatable->getMultiple());
    }

    public function testSetMultiple()
    {
        $multiple = array(
            'delete' => array(
                'title' => 'Delete',
                'route' => 'multiple_delete_route' // path to multiple delete route
            )
        );
        $this->_datatable->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "description"  => 'p.description',
                            "_identifier_" => 'p.id')
                )
                ->setMultiple($multiple);
        $this->assertEquals($multiple, $this->_datatable->getMultiple());
    }

    public function testGetConfiguration()
    {
        $this->_datatable->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "description"  => 'p.description',
                            "_identifier_" => 'p.id')
        );
        $this->assertEquals(array('all' => array('action' => true, 'search' => false), 'js' => array()), $this->_datatable->getConfiguration());
    }

    public function testGetSearchFields()
    {
        $this->assertEquals(array(), $this->_datatable->getSearchFields());
    }

    public function testSetSearchFields()
    {
        $this->_datatable->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "description"  => 'p.description',
                            "_identifier_" => 'p.id')
                )->setSearchFields(array(0));
        $this->assertEquals(array(0), $this->_datatable->getSearchFields());
    }

    public function testSetRenderders()
    {
        $out  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Feature', 'f')
                ->setFields(
                        array(
                            "title"        => 'f.name',
                            "_identifier_" => 'f.id')
                )
                ->setRenderers(
                        array(
                            1 => array(
                                'view'   => 'AliDatatableBundle:Renderers:_actions.html.twig',
                                'params' => array(
                                    'edit_route'            => 'alidatatable_test_edit',
                                    'delete_route'          => 'alidatatable_test_delete',
                                    'delete_form_prototype' => $this->_datatable->getPrototype('delete_form')
                                ),
                            ),
                        )
                )
                ->execute()
        ;
        $json = (array) json_decode($out->getContent());
        $this->assertContains('form', $json['aaData'][0][1]);
    }

    public function testSetRenderer()
    {
        $datatable  = $this->_datatable;
        $templating = $this->_container->get('templating');
        $out        = $datatable
                ->setEntity('Ali\DatatableBundle\Tests\TestBundle\Entity\Feature', 'f')
                ->setFields(
                        array(
                            "title"        => 'f.name',
                            "_identifier_" => 'f.id')
                )
                ->setRenderer(
                        function(&$data) use ($templating, $datatable) {
                    foreach ($data as $key => $value)
                    {
                        if ($key == 1)                                      // 1 => adress field
                        {
                            $data[$key] = $templating
                                    ->render(
                                    'AliDatatableBundle:Renderers:_actions.html.twig', array(
                                'edit_route'            => 'alidatatable_test_edit',
                                'delete_route'          => 'alidatatable_test_delete',
                                'delete_form_prototype' => $datatable->getPrototype('delete_form')
                                    )
                            );
                        }
                    }
                }
                )
                ->execute()
        ;
        $json = (array) json_decode($out->getContent());
        $this->assertContains('form', $json['aaData'][0][1]);
    }

}
