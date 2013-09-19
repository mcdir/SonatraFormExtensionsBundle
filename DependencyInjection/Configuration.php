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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sonatra_form_extensions');

        $this->addProfilerSection($rootNode);
        $this->addSelect2($rootNode);

        return $treeBuilder;
    }

    /**
     * Add profiler section.
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addProfilerSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('profiler')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultValue('%kernel.debug%')->end()
                        ->arrayNode('engines')
                            ->info('Replacing the renderer engine service id by a traceable version service id')
                            ->fixXmlConfig('engine')
                            ->useAttributeAsKey('id')
                            ->prototype('scalar')->end()
                            ->defaultValue(array('twig.form.engine' => 'sonatra_form_extensions.twig.tracable_engine'))
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Add configuration Select2.
     *
     * @param ArrayNodeDefinition $rootNode
     */
    private function addSelect2(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('select2')
                    ->canBeUnset()
                    ->treatNullLike(array('enabled' => true))
                    ->treatTrueLike(array('enabled' => true))
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
