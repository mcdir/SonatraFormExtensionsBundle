<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\Extension;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Form\EventListener\FixStringInputListener;
use Sonatra\Bundle\FormExtensionsBundle\Event\GetAjaxChoiceListEvent;
use Sonatra\Bundle\AjaxBundle\AjaxEvents;

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
     * @param FormFactory        $factory
     * @param ContainerInterface $container
     * @param string             $type
     * @param integer            $defaultPageSize
     */
    public function __construct(FormFactory $factory, ContainerInterface $container, $type, $defaultPageSize = 10)
    {
        parent::__construct($container, $type, $defaultPageSize);

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

        $builder->addEventSubscriber(new FixStringInputListener());
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

        $choiceList = $form->getConfig()->getAttribute('prototype')->getConfig()->getOption('choice_list');

        if (null === $choiceList) {
            $choiceList = new AjaxSimpleChoiceList($options['select2']['tags']);
            $choiceList->setAllowAdd($options['allow_add']);
            $choiceList->setAjax($options['select2']['ajax']);
            $choiceList->setPageSize($options['select2']['page_size']);
            $choiceList->setPageNumber(1);
            $choiceList->setSearch('');
            $choiceList->setIds(array());
        }

        $view->vars = array_replace($view->vars, array(
            'multiple'         => $options['multiple'],
            'choice_list'      => $choiceList,
            'choices_selected' => $choiceList->getLabelChoicesForValues((array) $view->vars['value']),
            'select2'          => array_merge($view->vars['select2'], array(
                'tags' => $choiceList->getDataChoices(),
            )),
        ));

        if ($options['select2']['ajax']) {
            $ajaxId = null !== $options['select2']['ajax_id'] ? $options['select2']['ajax_id'] : $view->vars['id'];
            $event = new GetAjaxChoiceListEvent($ajaxId, $this->request, $choiceList);

            $this->dispatcher->dispatch(AjaxEvents::INJECTION, $event);
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
            'options'   => function (Options $options, $value) {
                if ($options['select2']['enabled']) {
                    $dOptions = $this->factory->createBuilder($options['type'], null, $value)->getOptions();

                    if (isset($dOptions['select2'])) {
                        $value = array_merge($value, array(
                            'multiple'  => false,
                            'select2'   => array_merge(array_key_exists('select2', $value) ? $value['select2'] : array(), array(
                                'enabled'   => true,
                                'ajax'      => $options['select2']['ajax'],
                                'page_size' => $options['select2']['page_size'],
                                'allow_add' => true,
                            )),
                        ));
                    }

                    $value['error_bubbling'] = true;
                }

                return $value;
            },
            'allow_add' => function (Options $options, $value) {
                return $value;
            },
        ));
    }
}
