<?php

namespace Ali\DatatableBundle\Util\Formatter;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Renderer
{

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $_container;

    /** @var array */
    protected $_renderers = NULL;

    /** @var array */
    protected $_fields = NULL;

    /** @var int */
    protected $_identifier_index = NULL;

    /**
     * class constructor
     * 
     * @param ContainerInterface $container
     * @param array $renderers 
     * @param array $fields 
     */
    public function __construct(ContainerInterface $container, array $renderers, array $fields)
    {
        $this->_container = $container;
        $this->_renderers = $renderers;
        $this->_fields = $fields;
        $this->_prepare();
    }

    /**
     * return the rendered view using the given content
     * 
     * @param string    $view_path
     * @param array     $params
     * 
     * @return string
     */
    public function _applyView($view_path, array $params)
    {
        $out = $this->_container
                        ->get('templating')
                        ->render($view_path, $params);
        $out = html_entity_decode($out);
        return $out;
    }

    /**
     * prepare the renderer :
     *  - guess the identifier index
     * 
     * @return void
     */
    protected function _prepare()
    {
        $this->_identifier_index = array_search("_identifier_", array_keys($this->_fields));
    }

    /**
     * apply foreach given cell content the given (if exists) view
     * 
     * @param array $data 
     * 
     * @return void
     */
    public function applyTo(array &$data)
    {
        foreach ($data as $row_index => $row)
        {
            foreach ($row as $column_index => $column)
            {
                $params = array();
                if (array_key_exists($column_index, $this->_renderers))
                {
                    $view = $this->_renderers[$column_index]['view'];
                    $params = $this->_renderers[$column_index]['params'];
                }
                else
                {
                    $view = 'AliDatatableBundle:Renderers:_default.html.twig';
                }
                $params = array_merge($params, array('dt_item' => $data[$row_index][$column_index]));
                $data[$row_index][$column_index] = $this->_applyView(
                        $view, $params
                );
            }
        }
    }

}