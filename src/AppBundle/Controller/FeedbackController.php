<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FeedbackController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function saveAction(Request $request)
    {
        $post = $request->request;
        $this->get('sql')->execute(
            <<<'SQL'
            INSERT INTO `_feedback` (email, text, time, ip)
            VALUE (:email, :text, NOW(), :ip)
SQL
            ,
            [
                'email' => $post->get('email'),
                'text' => $post->get('text'),
                'ip' => $request->getClientIp(),
            ]
        );

        return new Response();
    }
}
