<?php

namespace Ali\DatatableBundle\Tests;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = array(
            // vendors
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Ali\DatatableBundle\AliDatatableBundle(),
            // for test purposes
            new \Ali\DatatableBundle\Tests\TestBundle\TestBundle()
        );

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function getRootDir()
    {
        return __DIR__;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/' . Kernel::VERSION . '/cache/' . $this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return sys_get_temp_dir() . '/' . Kernel::VERSION . '/logs';
    }

    /**
     * {@inheritdoc}
     */
    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();
        return $parameters;
    }

}
