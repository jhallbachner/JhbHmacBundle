<?php

namespace Jhb\HmacBundle;

use Jhb\HmacBundle\DependencyInjection\Security\Factory\HmacFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class JhbHmacBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new HmacFactory());
    }

    public function registerCommands(Application $application)
    {

    }
}
