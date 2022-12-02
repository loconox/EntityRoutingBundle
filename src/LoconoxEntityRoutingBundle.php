<?php

namespace Loconox\EntityRoutingBundle;

use Loconox\EntityRoutingBundle\DependencyInjection\Compiler\LoaderResolverCompilerPass;
use Loconox\EntityRoutingBundle\DependencyInjection\Compiler\SlugServiceRegistrationCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LoconoxEntityRoutingBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SlugServiceRegistrationCompilerPass());
        $container->addCompilerPass(new LoaderResolverCompilerPass());
    }
}
