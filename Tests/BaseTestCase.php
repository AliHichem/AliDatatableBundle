<?php

namespace Ali\DatatableBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Ali\DatatableBundle\Tests\TestBundle\Entity;

class BaseTestCase extends WebTestCase
{

    /** @var \Doctrine\ORM\EntityManager */
    protected $_em;

    /** @var ContainerInterface */
    protected $_container;

    /**
     * Creates a Kernel.
     *
     * Available options:
     *
     *  * environment
     *  * debug
     *
     * @param array $options An array of options
     *
     * @return HttpKernelInterface A HttpKernelInterface instance
     */
    static protected function createKernel(array $options = array())
    {
        return new AppKernel('test', true);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $kernel           = static::createKernel();
        $kernel->boot();
        $this->_container = $kernel->getContainer();
        $this->_em        = $this->_container->get('doctrine.orm.entity_manager');
        AnnotationRegistry::registerFile($kernel->getRootDir() . "/../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php");
        if (!isset($GLOBALS['TEST_CHARGED']))
        {
            $this->_createSchemas();
            $this->_insertData();
            $GLOBALS['TEST_CHARGED'] = true;
        }
    }

    /**
     * create schema from annotation mapping files
     * @return void
     */
    protected function _createSchemas()
    {
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->_em);
        $classes    = array(
            $this->_em->getClassMetadata("\Ali\DatatableBundle\Tests\TestBundle\Entity\Category"),
            $this->_em->getClassMetadata("\Ali\DatatableBundle\Tests\TestBundle\Entity\Product"),
            $this->_em->getClassMetadata("\Ali\DatatableBundle\Tests\TestBundle\Entity\Feature"),
        );
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);
    }

    protected function _insertData()
    {
        $em = $this->_em;
        $c  = (new Entity\Category)
                ->setName('CatA');
        $p  = (new Entity\Product)
                ->setName('Laptop')
                ->setPrice(1000)
                ->setDescription('New laptop')
                ->setCategory($c);
        $f  = (new Entity\Feature)
                ->setName('CPU I7 Generation')
                ->setProduct($p);
        $f1 = (new Entity\Feature)
                ->setName('SolidState drive')
                ->setProduct($p);
        $f2 = (new Entity\Feature)
                ->setName('SLI graphic card ')
                ->setProduct($p);
        $em->persist($c);
        $em->persist($p);
        $em->persist($f);
        $em->persist($f1);
        $em->persist($f2);
        $em->flush();
    }

}
