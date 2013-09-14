<?php

namespace Jhb\HmacBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\DefinitionDecorator;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class JhbHmacExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('jhb_hmac.user_provider.users', $config['users']);
        $container->setParameter('jhb_hmac.encoder.hashmethod', $config['hashMethod']);
        $container->setParameter('jhb_hmac.encoder.requiredate', $config['requireDate']);
        $container->setParameter('jhb_hmac.encoder.datewindow', $config['dateWindow']);
        $container->setParameter('jhb_hmac.encoder.datefield', $config['dateField']);
        $container->setParameter('jhb_hmac.encoder.keyfield', $config['keyField']);
        $container->setParameter('jhb_hmac.encoder.signaturefield', $config['signatureField']);
        $container->setParameter('jhb_hmac.encoder.requireheader', $config['requireHeaderCredentials']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
