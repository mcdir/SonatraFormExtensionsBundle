<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Form\EventListener\FixStringInputListener;
use Sonatra\Bundle\FormExtensionsBundle\Event\GetAjaxChoiceListEvent;
use Sonatra\Bundle\AjaxBundle\AjaxEvents;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CollectionSelect2Type extends AbstractSelect2Type
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new FixStringInputListener());
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $choiceList = $form->getConfig()->getAttribute('prototype')->getConfig()->getOption('choice_list');

        if (null === $choiceList) {
            $choiceList = new AjaxSimpleChoiceList($options['tags']);
            $choiceList->setAllowAdd($options['allow_add']);
            $choiceList->setAjax($options['ajax']);
            $choiceList->setPageSize($options['page_size']);
            $choiceList->setPageNumber(1);
            $choiceList->setSearch('');
            $choiceList->setIds(array());
        }

        $view->vars = array_replace($view->vars, array(
            'multiple'         => $options['multiple'],
            'tags'             => $choiceList->getDataChoices(),
            'choice_list'      => $choiceList,
            'choices_selected' => $choiceList->getLabelChoicesForValues((array) $view->vars['value']),
        ));

        if ($options['ajax']) {
            $ajaxId = null !== $options['ajax_id'] ? $options['ajax_id'] : $view->vars['id'];
            $event = new GetAjaxChoiceListEvent($ajaxId, $this->request, $choiceList);

            $this->dispatcher->dispatch(AjaxEvents::INJECTION, $event);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
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
            'allow_add'         => true,
            'allow_delete'      => true,
            'prototype'         => true,
            'error_bubbling'    => false,
            'multiple'          => true,
            'tags'              => array(),
        ));

        $resolver->setAllowedTypes(array(
            'multiple' => 'bool',
            'tags'     => 'array',
        ));

        $resolver->setNormalizers(array(
            'prototype' => function (Options $options, $value) {
                return true;
            },
            'compound'  => function (Options $options, $value) {
                return true;
            },
            'multiple'  => function (Options $options, $value) {
                return true;
            },
            'type'      => function (Options $options, $value) {
                if (in_array($value, array('choice', 'language', 'country', 'timezone', 'locale'))) {
                    return $value . '_select2';
                }

                return $value;
            },
            'options' => function (Options $options, $value) {
                if (false !== strrpos($options['type'], '_select2')) {
                    $value = array_merge($value, array(
                        'ajax'      => $options['ajax'],
                        'page_size' => $options['page_size'],
                        'multiple'  => false,
                        'allow_add' => true,
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
