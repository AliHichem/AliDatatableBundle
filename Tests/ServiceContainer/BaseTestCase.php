<?php

namespace Ali\DatatableBundle\Tests\ServiceContainer;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Ali\DatatableBundle\Tests\AppKernel;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\EntityManager;

class BaseTestCase extends WebTestCase
{

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
    
    protected function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->_em = self::createTestEntityManager();
        $this->_container = $kernel->getContainer();
        $this->_container->set('doctrine.orm.entity_manager', $this->_em);
    }

    /**
     * @return EntityManager
     */
    static public function createTestEntityManager($paths = array())
    {
        if (!class_exists('PDO') || !in_array('sqlite', \PDO::getAvailableDrivers())) {
            self::markTestSkipped('This test requires SQLite support in your environment');
        }
        $config = new \Doctrine\ORM\Configuration();
        $config->setEntityNamespaces(array('SymfonyTestsDoctrine' => 'Symfony\Tests\Bridge\Doctrine\Fixtures'));
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('SymfonyTests\Doctrine');
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
        $config->setQueryCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
        $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());

        $params = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        return EntityManager::create($params, $config);
    }
    
}