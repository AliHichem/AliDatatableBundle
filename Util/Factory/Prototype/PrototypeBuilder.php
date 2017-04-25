<?php

namespace Ali\DatatableBundle\Util\Factory\Prototype;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PrototypeBuilder
{

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;
    
    /** @var string */
    protected $_prototype;

    /**
     * class constructor
     * 
     * @param ContainerInterface $container
     * @param string             $type 
     */
    public function __construct(ContainerInterface $container, $type)
    {
        $this->container = $container;
        $method = "_{$type}";
        $rc = new \ReflectionClass(__CLASS__);
        if ($rc->hasMethod($method))
        {
            $this->_prototype = $this->$method();
        }
        else
        {
            throw new \Exception(sprintf('prototype "%s" not found', $type));
        }
    }
    
    /**
     * to string class converter
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->_prototype;
    }

    /**
     * simple form delete prototype
     * 
     * @return string
     */
    protected function _delete_form()
    {

        if (version_compare(phpversion(), '5.5', '<')) {
            return $this->container
                ->get('templating.helper.form')
                ->widget(
                    $this->container->get('form.factory')->createBuilder('form', array('id' => '@id'), array())
                        ->add('id', 'hidden')
                        ->getForm()
                        ->createView()
                );
        }
        else {
            return $this->container
                ->get('templating.helper.form')
                ->widget(
                    $this->container->get('form.factory')->createBuilder('Symfony\Component\Form\Extension\Core\Type\FormType', array('id' => '@id'), array())
                        ->add('id', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
                        ->getForm()
                        ->createView()
                );
        }
    }

}
