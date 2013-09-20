<?php

namespace Jhb\HmacBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('jhb_hmac');

        $rootNode
            ->fixXmlConfig('allowedSignatureLocation')
            ->children()
                ->scalarNode('hashMethod')->defaultValue('sha1')->end()
                ->booleanNode('requireDate')->defaultTrue()->end()
                ->scalarNode('dateWindow')->defaultValue(900)->end()
                ->scalarNode('dateField')->defaultValue('date')->end()
                ->scalarNode('keyField')->defaultValue('key')->end()
                ->scalarNode('signatureField')->defaultValue('signature')->end()
                ->arrayNode('allowedSignatureLocations')
                    ->requiresAtLeastOneElement()
                    ->defaultValue(array('header'))
                    ->prototype('scalar')
                        ->validate()
                            ->ifNotInArray(array('header', 'request', 'rest'))
                            ->thenInvalid('Allowable locations are "header," "request," and "rest"')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('users')
                    ->useAttributeAsKey('username')
                    ->fixXmlConfig('user')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('secretKey')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('publicKey')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('roles')
                                ->performNoDeepMerging()
                                ->beforeNormalization()->ifString()->then(function($v) { return array('value' => $v); })->end()
                                ->beforeNormalization()
                                    ->ifTrue(function($v) { return is_array($v) && isset($v['value']); })
                                    ->then(function($v) { return preg_split('/\s*,\s*/', $v['value']); })
                                ->end()
                                ->prototype('scalar')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
