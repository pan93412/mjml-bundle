<?php

namespace NotFloran\MjmlBundle\DependencyInjection;

use NotFloran\MjmlBundle\Renderer\BinaryRenderer;
use NotFloran\MjmlBundle\Renderer\RendererInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;

class MjmlExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $rendererServiceId = null;

        if ($config['renderer'] === 'binary') {
            $rendererDefinition = new Definition(BinaryRenderer::class);
            $rendererDefinition->addArgument($config['options']['binary']);
            $rendererDefinition->addArgument($config['options']['minify']);
            $container->setDefinition($rendererDefinition->getClass(), $rendererDefinition);
            $rendererServiceId = $rendererDefinition->getClass();
        } else if ($config['renderer'] === 'service') {
            $rendererServiceId = $config['options']['service_id'];
        } else {
            throw new \LogicException(sprintf(
                'Unknown renderer "%s"',
                $config['renderer']
            ));
        }

        $container->setAlias(RendererInterface::class, $rendererServiceId);
        $container->setAlias('mjml', $rendererServiceId);
    }
}
