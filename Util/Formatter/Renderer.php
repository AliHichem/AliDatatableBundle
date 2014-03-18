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
        $this->_fields    = $fields;
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
    public function applyView($view_path, array $params)
    {
        $out = $this->_container
                ->get('templating')
                ->render($view_path, $params);
        return html_entity_decode($out);
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
     * @param array $objects
     * 
     * @return void
     */
    public function applyTo(array &$data, array $objects)
    {
        foreach ($data as $row_index => $row)
        {
            $identifier_raw = $data[$row_index][$this->_identifier_index];
            foreach ($row as $column_index => $column)
            {
                $params = array();
                if (array_key_exists($column_index, $this->_renderers))
                {
                    $view   = $this->_renderers[$column_index]['view'];
                    $params = isset($this->_renderers[$column_index]['params']) ? $this->_renderers[$column_index]['params'] : array();
                }
                else
                {
                    $view = 'AliDatatableBundle:Renderers:_default.html.twig';
                }
                $params                          = array_merge($params, array(
                    'dt_obj'  => $objects[$row_index],
                    'dt_item' => $data[$row_index][$column_index],
                    'dt_id'   => $identifier_raw
                        )
                );
                $data[$row_index][$column_index] = $this->applyView(
                        $view, $params
                );
            }
        }
    }

}
