<?php

namespace AppBundle\Utils;

use AppKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceContainer
{
    /** @var ContainerInterface */
    private static $serviceContainer;

    /**
     * @return ContainerInterface
     */
    public static function get()
    {
        if (self::$serviceContainer === null) {
            $kernel = new AppKernel('dev', true);
//            $kernel->loadClassCache();
            $kernel->boot();
            self::$serviceContainer =
                $kernel->getContainer()->get('service_container');
        }

        return self::$serviceContainer;
    }
}
