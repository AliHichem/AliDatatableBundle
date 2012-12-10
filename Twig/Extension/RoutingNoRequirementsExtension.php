<?php

namespace Ali\DatatableBundle\Twig\Extension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Generates routes for Twig templates without using default route requirements.
 *
 * @category  Twig
 * @package   Wider Plan - AliDatatableBundle
 * @author    Benjamin Ugbene <benjamin.ugbene@kiddivouchers.com>
 * @copyright 2012 Wider Plan Limited
 * @license   Proprietary
 */
class RoutingNoRequirementsExtension extends \Twig_Extension
{
    private $generator;

    public function __construct(UrlGeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'urlNoReq'  => new \Twig_Function_Method($this, 'getUrlNoRequirements'),
            'pathNoReq' => new \Twig_Function_Method($this, 'getPathNoRequirements'),
        );
    }

    public function getPathNoRequirements($name, $parameters = array())
    {
        return $this->generator->generate($name, $parameters, false);
    }

    public function getUrlNoRequirements($name, $parameters = array())
    {
        return $this->generator->generate($name, $parameters, true);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'routingNoRequirements';
    }
}
