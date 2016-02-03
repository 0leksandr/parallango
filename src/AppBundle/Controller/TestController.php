<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
        var_dump($this->get('author')->getByIds([1, 2]));
    }
}
