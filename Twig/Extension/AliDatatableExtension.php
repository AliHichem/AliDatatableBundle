<?php

namespace Ali\DatatableBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            new \Twig_SimpleFunction('datatable', array($this, 'datatable'), array("is_safe" => array("html")))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('dta_trans', array($this, 'dtatransFilter'))
        );
    }

    /**
     * Datatable translate filter
     * 
     * @param string $id
     * 
     * @return string
     */
    public function dtatransFilter($id)
    {
        $translator = $this->_container->get('translator');
        $callback   = function($id) {
            $path = $this->_container->get('kernel')->locateResource('@AliDatatableBundle/Resources/translations/messages.en.yml');
            return \Symfony\Component\Yaml\Yaml::parse(file_get_contents($path))['ali']['common'][explode('.', $id)[2]];
        };
        return $translator->trans($id) === $id ? $callback($id) : $translator->trans($id);
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
        $dt                       = Datatable::getInstance($options['id']);
        $config                   = $dt->getConfiguration();
        $options['js_conf']       = json_encode($config['js']);
        $options['js']            = json_encode($options['js']);
        $options['action']        = $dt->getHasAction();
        $options['action_twig']   = $dt->getHasRendererAction();
        $options['fields']        = $dt->getFields();
        $options['delete_form']   = $this->createDeleteForm('_id_')->createView();
        $options['search']        = $dt->getSearch();
        $options['search_fields'] = $dt->getSearchFields();
        $options['multiple']      = $dt->getMultiple();
        $options['sort']          = is_null($dt->getOrderField()) ? NULL : array(array_search(
                    $dt->getOrderField(), array_values($dt->getFields())), $dt->getOrderType());
        $main_template            = 'AliDatatableBundle:Main:index.html.twig';
        if (isset($options['main_template']))
        {
            $main_template = $options['main_template'];
        }
        $session                  = $this->_container->get('session');
        $rawjs                    = $this->_container
                ->get('templating')
                ->render('AliDatatableBundle:Internal:script.html.twig', $options);
        $sess_dtb                 = $session->get('datatable', array());
        $sess_dtb[$options['id']] = $rawjs;
        $session->set('datatable', $sess_dtb);

        return $this->_container
                        ->get('templating')
                        ->render($main_template, $options);
    }

    /**
     * create delete form
     * 
     * @param type $id
     * @return type 
     */
    private function createDeleteForm($id)
    {
        if (version_compare(phpversion(), '5.5', '<')) {
            return $this->createFormBuilder(array('id' => $id))
                ->add('id', 'hidden')
                ->getForm();
        }
        else {
            return $this->createFormBuilder(array('id' => $id))
                ->add('id', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
                ->getForm();
        }
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
        if (version_compare(phpversion(), '5.5', '<')) {
            return $this->_container->get('form.factory')->createBuilder('form', $data, $options);
        }
        else {
            return $this->_container->get('form.factory')->createBuilder('Symfony\Component\Form\Extension\Core\Type\FormType', $data, $options);
        }
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
