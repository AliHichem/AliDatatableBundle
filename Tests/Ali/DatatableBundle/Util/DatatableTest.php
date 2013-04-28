<?php

namespace Ali\DatatableBundle\Util;

use Ali\DatatableBundle\BaseTestCase;

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

    public function test_chainingClassBehavior()
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

    public function test_addJoin()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->addJoin('p.features', 'f');

        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb    = $this->_datatable->getQueryBuilder()->getDoctrineQueryBuilder();
        $parts = $qb->getDQLParts();
        $this->assertNotEmpty($parts['join']);
        $this->assertTrue(array_key_exists('p', $parts['join']));
    }

    public function test_execute()
    {
        $r = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
                )
                ->execute();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $r);
    }

    public function test_getInstance()
    {
        $this->_datatable
                ->setDatatableId('test')
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $i = $this->_datatable->getInstance('test');
        $this->assertInstanceOf('\Ali\DatatableBundle\Util\Datatable', $i);
        $this->assertEquals('p', $i->getEntityAlias());
    }

    public function test_getEntityName()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertEquals('Ali\DatatableBundle\Entity\Product', $this->_datatable->getEntityName());
    }

    public function test_getEntityAlias()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertEquals('p', $this->_datatable->getEntityAlias());
    }

    public function test_getFields()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertInternalType('array', $this->_datatable->getFields());
    }

    public function test_getHasAction()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertInternalType('boolean', $this->_datatable->getHasAction());
    }

    public function test_getHasRendererAction()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id')
        );
        $this->assertInternalType('boolean', $this->_datatable->getHasRendererAction());
    }

    public function test_getOrderField()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInternalType('string', $this->_datatable->getOrderField());
    }

    public function test_getOrderType()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInternalType('string', $this->_datatable->getOrderType());
    }

    public function test_getPrototype()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInstanceOf('Ali\DatatableBundle\Util\Factory\Prototype\PrototypeBuilder', $this->_datatable->getPrototype('delete_form'));
    }

    public function test_getQueryBuilder()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInstanceOf('Ali\DatatableBundle\Util\Factory\Query\DoctrineBuilder', $this->_datatable->getQueryBuilder());
    }

    public function test_getSearch()
    {
        $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Product', 'p')
                ->setFields(
                        array(
                            "title"        => 'p.name',
                            "_identifier_" => 'p.id'))
                ->setOrder('p.id', 'asc')
        ;
        $this->assertInternalType('boolean', $this->_datatable->getSearch());
    }

    public function test_setRenderders()
    {
        $out  = $this->_datatable
                ->setEntity('Ali\DatatableBundle\Entity\Feature', 'f')
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
                                    'edit_route'            => '_edit',
                                    'delete_route'          => '_delete',
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

    public function test_setRenderer()
    {
        $datatable  = $this->_datatable;
        $templating = $this->_container->get('templating');
        $out        = $datatable
                ->setEntity('Ali\DatatableBundle\Entity\Feature', 'f')
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
                                        'edit_route'            => '_edit',
                                        'delete_route'          => '_delete',
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