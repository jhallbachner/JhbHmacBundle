<?php

namespace Jhb\HmacBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;

class HmacFactory implements SecurityFactoryInterface
{
	public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint)
	{
		$providerId = 'security.authentication.provider.jhb_hmac.' . $id;
		$container
			->setDefinition($providerId, new DefinitionDecorator('jhb_hmac.security.authentication.provider'))
			->replaceArgument(0, new Reference($userProvider));

		$listenerId = 'security.authentication.listener.jhb_hmac.' . $id;
		$container
			->setDefinition($listenerId, new DefinitionDecorator('jhb_hmac.security.authentication.listener'));

		return array($providerId, $listenerId, $defaultEntryPoint);
	}

	public function getPosition()
	{
		return 'pre_auth';
	}

	public function getKey()
	{
		return 'hmac';
	}

	public function addConfiguration(NodeDefinition $node)
	{

	}
}