<?php


namespace Ali\DatatableBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AliDatatableExtension extends \Twig_Extension
{
    private $container;
    
    /**
     * class constructor 
     * 
     * @param ContainerInterface $container 
     */
    public function __construct( ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'datatable' => new \Twig_Function_Method($this, 'datatable')
        );
    }
    
    /**
     * Converts a string to time
     * 
     * @param string $string
     * @return int 
     */
    public function datatable ($options)
    {
        /* @var $datatable \Ali\DatatableBundle\Util\Datatable */
        $datatable = $this->container->get('datatable');
        
        $options['js'] = json_encode($options['js']);
        $options['action'] = $datatable->getHasAction();
        $options['fields'] = $datatable->getFields();
        $options['delete_form'] = $this->createDeleteForm('_id_')->createView();
        
        return $this->container
                ->get('templating')
                ->render(
                        'AliDatatableBundle:Main:index.html.twig', $options);
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
        return $this->container->get('form.factory')->createBuilder('form', $data, $options);
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