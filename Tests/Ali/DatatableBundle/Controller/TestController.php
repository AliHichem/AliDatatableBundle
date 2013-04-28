<?php

namespace Ali\DatatableBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class TestController extends Controller
{

    public function indexAction()
    {
        return new Response('test');
    }

    public function editAction($id)
    {
        return new Response('test');
    }

    public function deleteAction($id)
    {
        return new Response('test');
    }

}