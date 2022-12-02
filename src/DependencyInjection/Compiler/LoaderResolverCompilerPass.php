<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Loconox\EntityRoutingBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;


class LoaderResolverCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('loconox_entity_routing.route.loader_resolver')) {
            return;
        }

        $definition = $container->getDefinition('loconox_entity_routing.route.loader_resolver');

        foreach ($container->findTaggedServiceIds('loconox_entity_routing.loader') as $id => $attributes) {
            $definition->addMethodCall('addLoader', [new Reference($id)]);
        }
    }
}
