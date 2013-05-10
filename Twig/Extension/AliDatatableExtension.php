<?php

namespace Ali\DatatableBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ali\DatatableBundle\Util\Datatable;

class AliDatatableExtension extends \Twig_Extension
{

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $_container;

    /**
     * class constructor 
     * 
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'datatable' => new \Twig_Function_Method($this, 'datatable', array("is_safe" => array("html")))
        );
    }

    /**
     * Converts a string to time
     * 
     * @param string $string
     * @return int 
     */
    public function datatable($options)
    {
        if (!isset($options['id']))
        {
            $options['id'] = 'ali-dta_' . md5(rand(1, 100));
        }
        $datatable                = Datatable::getInstance($options['id']);
        $config                   = $datatable->getConfiguration();
        $options['js_conf']       = json_encode($config['js']);
        $options['js']            = json_encode($options['js']);
        $options['action']        = $datatable->getHasAction();
        $options['action_twig']   = $datatable->getHasRendererAction();
        $options['fields']        = $datatable->getFields();
        $options['delete_form']   = $this->createDeleteForm('_id_')->createView();
        $options['search']        = $datatable->getSearch();
        $options['search_fields'] = $datatable->getSearchFields();
        $options['multiple']      = $datatable->getMultiple();
        $main_template            = 'AliDatatableBundle:Main:index.html.twig';
        if (isset($options['main_template']))
        {
            $main_template = $options['main_template'];
        }

        return $this->_container
                        ->get('templating')
                        ->render(
                                $main_template, $options);
    }

    /**
     * create delete form
     * 
     * @param type $id
     * @return type 
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm();
    }

    /**
     * create form builder
     * 
     * @param type $data
     * @param array $options
     * @return type 
     */
    public function createFormBuilder($data = null, array $options = array())
    {
        return $this->_container->get('form.factory')->createBuilder('form', $data, $options);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'DatatableBundle';
    }

}