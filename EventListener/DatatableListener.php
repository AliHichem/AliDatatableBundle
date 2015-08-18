<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ali\DatatableBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\AutoExpireFlashBag;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * DatatableListener injects the js content to the bottom of the body element.
 * (heavely copied from WebDebugToolbarListener from core Symfony2 core)
 * 
 * @see \Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener 
 * @author Ali Hichem <ali.hichem@mail.com>
 */
class DatatableListener implements EventSubscriberInterface
{

    /**
     * On kernel response event
     * 
     * @param FilterResponseEvent $event
     * @return void|null
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();
        $request  = $event->getRequest();
        if (!$event->isMasterRequest())
        {
            return;
        }
        if ($request->isXmlHttpRequest())
        {
            return;
        }
        $this->_injectDatatableScript($response, $request);
    }

    /**
     * Injects the datatable scripts into the given HTTP Response.
     *
     * @param Response $response A Response instance
     */
    protected function _injectDatatableScript(Response $response, Request $request)
    {
        $content  = $response->getContent();
        $pos_body = strripos($content, '</body>');
        if (!$pos_body)
        {
            return;
        }
        $session       = $request->getSession();
        $dom           = '<script id="alidatatable-scripts" type="text/javascript">';
        $pos_container = strripos($content, 'alidatatable-scripts');
        $sess_dta      = $session->get('datatable',array());
        $dta_script    = null;
        if ($sess_dta)
        {
            array_walk($sess_dta, function(&$part, &$key) {
                $part = trim(preg_replace('/\s\s+/', ' ', $part));
            });
            $dta_script = implode("\n", $sess_dta);
        }
        $session->set('datatable', array());
        if (!$pos_container)
        {
            $dta_container = $dom;
            $content       = substr_replace($content, $dta_container . '</script>', $pos_body, 0);
            $response->setContent($content);
        }
        $pos_dta = strripos($content, $dom);
        $content = substr_replace($content, $dta_script, $pos_dta + strlen($dom), 0);
        $response->setContent($content);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::RESPONSE => array('onKernelResponse', -127),
        );
    }

}
