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
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataMapper\CollectionMapper;
use Sonatra\Bundle\FormExtensionsBundle\Form\EventListener\StringResizeFormListener;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\ArrayToStringTransformer;

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
        $builder->setDataMapper(new CollectionMapper());
        $builder->addEventSubscriber(new StringResizeFormListener($options['required']));

        if (!$options['ajax']) {
            $builder->addViewTransformer(new ArrayToStringTransformer($options['required']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars = array_replace($view->vars, array(
            'multiple' => $options['multiple'],
            'tags'     => $options['ajax'] ? $options['tags'] : $options['choice_list']->getDataChoices(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $selected = (array) (is_string($view->vars['value']) ? explode(',', $view->vars['value']) : $view->vars['value']);

        if (!$options['ajax']) {
            $view->vars['choices_selected'] = $options['choice_list']->getLabelChoicesForValues($selected);
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
            'prototype'         => false,
            'error_bubbling'    => false,
            'multiple'          => true,
            'choice_list'       => function (Options $options) {
                $tags = null !== $options['tags'] ? $options['tags'] : array();

                return new SimpleChoiceList($tags, $options['preferred_choices']);
            },
            'preferred_choices' => array(),
            'tags'              => array(),
        ));

        $resolver->setAllowedTypes(array(
            'choice_list'       => array('null', 'Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface'),
            'preferred_choices' => 'array',
            'tags'              => 'array',
        ));

        $resolver->setNormalizers(array(
            'prototype'   => function (Options $options, $value) {
                return false;
            },
            'compound'    => function (Options $options, $value) {
                return true;
            },
            'multiple'    => function (Options $options, $value) {
                return true;
            },
            'choice_list' => function (Options $options, $value) {
                if (!$value instanceof AjaxChoiceListInterface) {
                    $value = new AjaxSimpleChoiceList($options['tags'], $options['preferred_choices']);
                }

                $value->setAjax($options['ajax']);
                $value->setPageSize($options['page_size']);
                $value->setPageNumber(1);
                $value->setSearch('');
                $value->setIds(array());

                return $value;
            },
        ));
    }
}
