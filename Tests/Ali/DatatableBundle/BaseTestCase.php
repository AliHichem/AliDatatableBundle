<?php

namespace Ali\DatatableBundle;

use Ali\DatatableBundle\Tests\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

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
        $this->_em        = self::createTestEntityManager();
        $this->_container = $kernel->getContainer();
        $this->_container->set('doctrine.orm.entity_manager', $this->_em);
        if (!isset($GLOBALS['TEST_CHARGED']))
        {
            $this->_createSchemas();
            $this->_insertData();
            $GLOBALS['TEST_CHARGED'] = true;
        }
    }

    /**
     * @return EntityManager
     */
    static public function createTestEntityManager($paths = array())
    {
        if (!class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers()))
        {
            self::markTestSkipped('This test requires SQLite support in your environment');
        }
        $paths  = array(realpath(__DIR__ . '/Entity'));
        $config = Setup::createAnnotationMetadataConfiguration($paths, false);
        $params = array(
            'driver'   => 'pdo_sqlite',
            'memory'   => true,
            'password' => '',
            'dbname'   => 'ali'
        );
        return EntityManager::create($params, $config);
    }

    /**
     * create schema from annotation mapping files
     * @return void
     */
    protected function _createSchemas()
    {
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->_em);
        $classes    = array(
            $this->_em->getClassMetadata("\Ali\DatatableBundle\Entity\Product"),
            $this->_em->getClassMetadata("\Ali\DatatableBundle\Entity\Feature"),
        );
        $schemaTool->dropSchema($classes);
        $schemaTool->createSchema($classes);
    }

    protected function _insertData()
    {
        $em = $this->_em;
        $p  = new Entity\Product;
        $p->setName('Laptop')
                ->setPrice(1000)
                ->setDescription('New laptop');
        $f  = new Entity\Feature;
        $f->setName('CPU I7 Generation')
                ->setProduct($p);
        $f1 = new Entity\Feature;
        $f1->setName('SolidState drive')
                ->setProduct($p);
        $f2 = new Entity\Feature;
        $f2->setName('SLI graphic card ')
                ->setProduct($p);
        $em->persist($p);
        $em->persist($f);
        $em->persist($f1);
        $em->persist($f2);
        $em->flush();
    }

}