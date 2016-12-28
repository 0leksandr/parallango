<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageAwareController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function forwardAction(Request $request)
    {
        $router = $this->container->get('router');
        $match = $router->match($request->get('path'));
        return $this->forward(
            $match['_controller'],
            [$match['_route']],
            $match
        );
    }
}
