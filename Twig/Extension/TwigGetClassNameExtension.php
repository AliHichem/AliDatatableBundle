<?php
/**
 * Created by PhpStorm.
 * User: Maarten
 * Date: 12-5-2017
 * Time: 17:26
 */

namespace Ali\DatatableBundle\Twig\Extension;

class TwigGetClassNameExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('get_class_name', array($this, 'getClassNameFilter')),
        );
    }

    /**
     * @param $object
     * @return string
     */
    public function getClassNameFilter($object)
    {
        return get_class($object);
    }

}