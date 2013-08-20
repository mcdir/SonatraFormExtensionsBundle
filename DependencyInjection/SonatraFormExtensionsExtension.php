<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraFormExtensionsExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('twig.yml');
        $loader->load('form.yml');

        foreach (array('select2') as $type) {
            if (isset($config[$type]) && !empty($config[$type]['enabled'])) {
                $method = 'register' . ucfirst($type) . 'Configuration';

                $this->$method($config[$type], $container);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $exts = $container->getExtensions();

        if (isset($exts['twig'])) {
            $resources = array();

            foreach (array('div') as $template) {
                $resources[] = 'SonatraFormExtensionsBundle:Form:form_' . $template . '_layout.html.twig';
            }

            $container->prependExtensionConfig(
                    'twig',
                    array('form'  => array('resources' => $resources))
            );
        }
    }

    /**
     * Register Select2 configuration.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    private function registerSelect2Configuration(array $configs, ContainerBuilder $container)
    {
        $serviceId = 'sonatra.formextensions.type.select2';

        foreach ($this->getChoiceTypeNames() as $type) {
            $typeDef = new DefinitionDecorator($serviceId);
            $typeDef
                ->addArgument($type)
                ->addTag('form.type', array('alias' => $type.'_select2'))
            ;

            $container->setDefinition($serviceId.'.'.$type, $typeDef);
        }
    }

    /**
     * Get the names of the standard form choice type.
     *
     * @return array
     */
    private function getChoiceTypeNames()
    {
        return array('choice', 'language', 'country', 'timezone', 'locale', 'entity', 'collection');
    }
}
