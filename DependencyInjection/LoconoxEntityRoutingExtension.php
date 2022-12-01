<?php

namespace Loconox\EntityRoutingBundle\DependencyInjection;

use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class LoconoxEntityRoutingExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('router.yaml');
        $loader->load('slug.yaml');
        $loader->load('event.yaml');
        $loader->load('admin.yaml');
        $loader->load('validator.yaml');
        $loader->load('orm.yaml');
        $loader->load('twig.yaml');


        $container->setParameter('loconox_entity_routing.entity_manager', $config['entity_manager']);
        $container->setParameter('loconox_entity_routing.slug.class', $config['class']['slug']);
        $container->setParameter('loconox_entity_routing.router.resource', $config['router']['resource']);
        $container->setParameter('loconox_entity_routing.router.resource_type', $config['router']['type']);

        //$this->registerDoctrineMapping($config, $container);
    }

    public function registerDoctrineMapping(array $config)
    {
        $collector = DoctrineCollector::getInstance();

        foreach ($config['class'] as $type => $class) {
            if (!class_exists($class)) {
                return;
            }
        }

        $collector->addAssociation($config['class']['slug'], 'mapOneToOne', array(
                'fieldName' => 'old',
                'targetEntity' => $config['class']['slug'],
                'mappedBy' => 'new',
                'inversedBy' => NULL,
                'fetch' => 'EAGER',
            ));

        $collector->addAssociation($config['class']['slug'], 'mapOneToOne', array(
            'fieldName' => 'new',
            'targetEntity' => $config['class']['slug'],
            'mappedBy' => NULL,
            'inversedBy' => 'old',
            'fetch' => 'EAGER',
        ));
    }
}
