<?php

namespace Ali\DatatableBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default controller.
 *
 */
class DefaultController extends Controller
{

    /**
     * index action.
     *
     */
    public function indexAction()
    {
        return new Response('test');
    }

}
