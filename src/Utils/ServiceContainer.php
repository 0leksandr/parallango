<?php

namespace Utils;

use AppKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ServiceContainer
{
    /** @var ContainerInterface[] */
    private static $serviceContainers = [];

    /**
     * @param string $environment
     * @return ContainerInterface
     */
    public static function get($environment)
    {

        $container = &self::$serviceContainers[$environment];
        if (!isset($container)) {
            $kernel = new AppKernel($environment, true);
//            $kernel->loadClassCache();
            $kernel->boot();
            $container = $kernel->getContainer()->get('service_container');
        }

        return $container;
    }
}
