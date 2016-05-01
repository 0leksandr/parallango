<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Utils\ServiceContainer;

require_once __DIR__ . '/../../Utils/Utils.php';

class TestController extends Controller
{
    public function indexAction(Request $request)
    {
        ob_start();
        $this->test();
        $text = ob_get_clean();
        echo $text;
        return $this->render('base.html.twig');
    }

    private function test()
    {
    }
}
