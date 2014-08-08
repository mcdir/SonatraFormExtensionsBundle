<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\Extension;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\Select2AjaxChoiceListFormatter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Form\EventListener\FixStringInputSubscriber;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CollectionSelect2TypeExtension extends AbstractSelect2TypeExtension
{
    /**
     * @var FormFactory
     */
    protected $factory;

    /**
     * Constructor.
     *
     * @param FormFactoryInterface     $factory
     * @param EventDispatcherInterface $dispatcher
     * @param Request                  $request
     * @param RouterInterface          $router
     * @param string                   $type
     * @param integer                  $defaultPageSize
     */
    public function __construct(FormFactoryInterface $factory, EventDispatcherInterface $dispatcher, Request $request, RouterInterface $router, $type, $defaultPageSize = 10)
    {
        parent::__construct($dispatcher, $request, $router, $type, $defaultPageSize);

        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['select2']['enabled']) {
            return;
        }

        $builder->addEventSubscriber(new FixStringInputSubscriber());

        $choiceList = $builder->getAttribute('prototype')->getConfig()->getOption('choice_list');
        $routeName = $builder->getAttribute('prototype')->getConfig()->getAttribute('select2_ajax_route');

        if (null === $choiceList) {
            $tags = $options['select2']['tags'];
            $tags = isset($tags) ? $tags : array();
            $choiceList = new AjaxSimpleChoiceList(new Select2AjaxChoiceListFormatter(), $tags);
            $choiceList->setAllowAdd($options['allow_add']);
            $choiceList->setPageSize($options['select2']['page_size']);
            $choiceList->setPageNumber(1);
            $choiceList->setSearch('');
            $choiceList->setIds(array());
            $choiceList->reset();
        }

        $builder->setAttribute('choice_list', $choiceList);

        if (null === $options['select2']['ajax_route'] && $options['select2']['ajax']
                && null !== $options['type'] && null !== $routeName) {
            $builder->setAttribute('select2_ajax_route', $routeName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        if (!$options['select2']['enabled']) {
            return;
        }

        $choiceList = $form->getConfig()->getAttribute('choice_list');

        $view->vars = array_replace($view->vars, array(
            'multiple'         => $options['multiple'],
            'choice_list'      => $choiceList,
            'choices_selected' => $choiceList->getFormattedChoicesForValues((array) $view->vars['value']),
            'select2'          => array_merge($view->vars['select2'], array(
                'tags' => $choiceList->getFormattedChoices(),
            )),
        ));

        if (!empty($view->vars['value'])) {
            $view->vars['value'] = $choiceList->getValuesForChoices($view->vars['value']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (!$options['select2']['enabled']) {
            return;
        }

        $view->vars['form']->vars['no_label_for'] = true;

        // convert array to string
        if (is_array($view->vars['value'])) {
            $view->vars['value'] = implode(',', $view->vars['value']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'allow_add'      => true,
            'allow_delete'   => true,
            'prototype'      => true,
            'error_bubbling' => false,
            'multiple'       => true,
            'select2'        => array(
                'enabled' => false,
                'tags'    => array(),
            ),
        ));

        $resolver->setAllowedTypes(array(
            'multiple' => 'bool',
        ));

        $factory = $this->factory;
        $resolver->setNormalizers(array(
            'prototype' => function (Options $options) {
                return true;
            },
            'compound'  => function (Options $options) {
                return true;
            },
            'multiple'  => function (Options $options) {
                return true;
            },
            'type'      => function (Options $options, $value) {
                return $value;
            },
            'options'   => function (Options $options, $value) use ($factory) {
                $dOptions = $factory->createBuilder($options['type'], null, $value)->getOptions();

                if (isset($dOptions['select2'])) {
                    $value = array_merge($value, array(
                        'multiple'  => false,
                        'select2'   => array_merge(array_key_exists('select2', $value) ? $value['select2'] : array(), array(
                            'enabled'             => $options['select2']['enabled'],
                            'ajax'                => $options['select2']['ajax'],
                            'ajax_route'          => $options['select2']['ajax_route'],
                            'ajax_parameters'     => $options['select2']['ajax_parameters'],
                            'ajax_reference_type' => $options['select2']['ajax_reference_type'],
                            'page_size'           => $options['select2']['page_size'],
                            'tags'                => $options['select2']['tags'],
                            'allow_add'           => $options['allow_add'],
                        )),
                    ));
                }

                $value['error_bubbling'] = true;

                return $value;
            },
            'allow_add' => function (Options $options, $value) {
                return $value;
            },
        ));
    }
}
