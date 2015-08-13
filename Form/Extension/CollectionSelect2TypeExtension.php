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

use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CollectionSelect2TypeExtension extends AbstractSelect2ConfigTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['select2']['enabled']) {
            return;
        }

        try {
            $selector = $builder->getFormFactory()->createBuilder($options['type'], null, array_merge(
                $options['options'], array(
                    'multiple' => true,
                ))
            );
            $builder->setAttribute('selector', $selector);
            $builder->setAttribute('choice_loader', $selector->getOption('choice_loader'));
        } catch (UndefinedOptionsException $e) {
            $msg = 'The "%s" type is not an "choice" with Select2 extension, because: %s';
            throw new InvalidConfigurationException(sprintf($msg, $options['type'], lcfirst($e->getMessage())), 0, $e);
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

        /* @var FormBuilderInterface $selectorBuilder */
        $selectorBuilder = $form->getConfig()->getAttribute('selector');
        $selectorBuilder->setData($form->getData());
        $selector = $selectorBuilder->getForm();
        $selectorView = $selector->createView($view);

        $selectorView->vars = array_replace($selectorView->vars, array(
            'id' => $view->vars['id'],
            'full_name' => $view->vars['full_name'].'[]',
        ));

        $view->vars = array_replace($view->vars, array(
            'selector' => $selectorView,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'type' => function (Options $options, $value) {
                return $options['select2']['enabled'] ? 'choice' : $value;
            },
            'allow_add' => function (Options $options, $value) {
                return $options['select2']['enabled'] ? true : $value;
            },
            'allow_delete' => function (Options $options, $value) {
                return $options['select2']['enabled'] ? true : $value;
            },
            'prototype' => function (Options $options, $value) {
                return $options['select2']['enabled'] ? false : $value;
            },
        ));

        $resolver->setNormalizer('prototype', function (Options $options, $value) {
            return $options['select2']['enabled'] ? false : $value;
        });

        $resolver->setNormalizer('options', function (Options $options, $value) {
            return $options['select2']['enabled']
                ? array_merge($value, array(
                    'error_bubbling' => false,
                    'multiple' => false,
                    'select2' => array_merge($options['select2'], array(
                        'tags' => $options['allow_add'],
                    )),
                ))
                : $value;
        });
    }
}
