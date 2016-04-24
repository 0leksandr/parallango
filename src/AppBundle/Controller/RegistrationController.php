<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../../Utils/Utils.php';

class RegistrationController extends Controller
{
    /**
     * @param Request $request
     * @return Response
     */
    public function registerUserAction(Request $request)
    {
        $sql = $this->get('sql');
        $post = $request->request;
        $password = $post->get('password');
        $passConfirmation = $post->get('password_confirmation');
        $login = $post->get('login');
        $email = $post->get('email');

        if ($password !== $passConfirmation) {
            // TODO: translate, return error code
            return new Response('Your password and confirmation do not match');
        }
        if ($sql->getSingle(
            <<<'SQL'
            SELECT COUNT(*)
            FROM `_users`
            WHERE login = :login
SQL
            ,
            ['login' => $login]
        ) > 0) {
            return new Response('This login is not free');
        }
        $sql->execute(
            <<<'SQL'
            INSERT INTO `_users` (login, email, password, ip, time, useragent)
            VALUE (:login, :email, :password, :ip, NOW(), :useragent)
SQL
            ,
            [
                'login' => $login,
                'email' => $email,
                'password' => $password,
                'ip' => $request->getClientIp(),
                'useragent' => getUseragent($request),
            ]
        );
        return new Response('success');
    }
}
