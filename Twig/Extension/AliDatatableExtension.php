<?php

namespace Ali\DatatableBundle\Twig\Extension;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ali\DatatableBundle\Util\Datatable;

class AliDatatableExtension extends \Twig_Extension {

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $_container;

    /**
     * class constructor 
     * 
     * @param ContainerInterface $container 
     */
    public function __construct(ContainerInterface $container) {
        $this->_container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions() {
        return array(
            'datatable' => new \Twig_Function_Method($this, 'datatable', array("is_safe" => array("html"))),
            'datatable_html' => new \Twig_Function_Method($this, 'datatableHtml', array("is_safe" => array("html"))),
            'datatable_js' => new \Twig_Function_Method($this, 'datatableJs', array("is_safe" => array("html")))
        );
    }

    /**
     * Render Datatable HTML and JS
     * 
     * @param array $options
     * @return Response 
     */
    public function datatable($options) {
        return $this->renderTemplate($template = "index", $options);
    }

    /**
     * Render Datatable HTML
     * 
     * @param array $options
     * @return Response 
     */
    public function datatableHtml($options) {
        return $this->renderTemplate($template = "html", $options);
    }

    /**
     * Render Datatable JS
     * 
     * @param array $options
     * @return Response 
     */
    public function datatableJs($options) {
        return $this->renderTemplate($template = "js", $options);
    }

    /**
     * Fullfill option
     * 
     * @param array $options
     * @return array
     */
    private function fullfillOptions(array $options) {
        if (!isset($options['id'])) {
            $options['id'] = 'ali-dta_' . md5(rand(1, 100));
        }
        $dt = Datatable::getInstance($options['id']);
        $config = $dt->getConfiguration();
        $options['js_conf'] = json_encode($config['js']);
        if(isset($options['js'])) {
            $options['js'] = json_encode($options['js']);
        }
        $options['action'] = $dt->getHasAction();
        $options['action_twig'] = $dt->getHasRendererAction();
        $options['fields'] = $dt->getFields();
        $options['delete_form'] = $this->createDeleteForm('_id_')->createView();
        $options['search'] = $dt->getSearch();
        $options['search_fields'] = $dt->getSearchFields();
        $options['multiple'] = $dt->getMultiple();
        $options['order'] = is_null($dt->getOrderField()) ? NULL : array(array_search(
                    $dt->getOrderField(), array_values($dt->getFields())), $dt->getOrderType());

        return $options;
    }

    /**
     * Render Template
     * 
     * @param string $template
     * @param array $options
     * @return Response
     */
    private function renderTemplate($template = 'index', array $options) {
        return $this->_container
                        ->get('templating')
                        ->render(
                                sprintf('AliDatatableBundle:Main:%s.html.twig', $template), $this->fullfillOptions($options));
    }

    /**
     * create delete form
     * 
     * @param type $id
     * @return type 
     */
    private function createDeleteForm($id) {
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
    public function createFormBuilder($data = null, array $options = array()) {
        return $this->_container->get('form.factory')->createBuilder('form', $data, $options);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName() {
        return 'DatatableBundle';
    }

}
