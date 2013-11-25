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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Sonatra\Bundle\AjaxBundle\AjaxEvents;
use Sonatra\Bundle\FormExtensionsBundle\Event\GetAjaxChoiceListEvent;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\ChoicesToValuesTransformer;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\ChoicesToStringTransformer;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\ChoiceToStringTransformer;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractSelect2Type extends AbstractType
{
    /**
     * @var string
     */
    protected $type;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var integer
     */
    protected $ajaxPageSize;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param string             $type
     * @param integer            $defaultPageSize
     */
    public function __construct(ContainerInterface $container, $type, $defaultPageSize = 10)
    {
        $this->type = $type;
        $this->dispatcher = $container->get('event_dispatcher');
        $this->request = $container->get('request');
        $this->ajaxPageSize = $defaultPageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['ajax']) {
            $builder->resetViewTransformers();

            if ($options['multiple']) {
                $builder->addViewTransformer(new ChoicesToStringTransformer($options['choice_list'], $options['required']));

            } else {
                $builder->addViewTransformer(new ChoiceToStringTransformer($options['choice_list'], $options['required']));
            }

        } elseif ($options['multiple']) {
            $builder->resetViewTransformers();

            $builder->addViewTransformer(new ChoicesToValuesTransformer($options['choice_list'], $options['required']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, array(
                'allow_clear'                => $options['required'] ? 'false' : 'true',
                'ajax'                       => $options['ajax'],
                'ajax_url'                   => $options['ajax_url'],
                'ajax_id'                    => $options['ajax_id'],
                'quiet_millis'               => $options['quiet_millis'],
                'page_size'                  => $options['page_size'],
                'close_on_select'            => $options['close_on_select'],
                'open_on_enter'              => $options['open_on_enter'],
                'container_css'              => $options['container_css'],
                'dropdown_css'               => $options['dropdown_css'],
                'container_css_class'        => $options['container_css_class'],
                'dropdown_css_class'         => $options['dropdown_css_class'],
                'format_result'              => $options['format_result'],
                'format_selection'           => $options['format_selection'],
                'format_result_css_class'    => $options['format_result_css_class'],
                'minimum_results_for_search' => $options['minimum_results_for_search'],
                'minimum_input_length'       => $options['minimum_input_length'],
                'maximum_selection_size'     => $options['maximum_selection_size'],
                'matcher'                    => $options['matcher'],
                'select_separator'           => $options['select_separator'],
                'token_separators'           => $options['token_separators'],
                'tokenizer'                  => $options['tokenizer'],
                'escape_markup'              => $options['escape_markup'],
                'blur_on_change'             => $options['blur_on_change'],
                'placeholder'                => escapeshellarg($options['placeholder']),
                'format_no_matches'          => $options['format_no_matches'],
                'format_input_too_short'     => $options['format_input_too_short'],
                'format_selection_too_big'   => $options['format_selection_too_big'],
                'format_load_more'           => $options['format_load_more'],
                'format_searching'           => $options['format_searching'],
                'select_id'                  => $options['select_id'],
                'create_search_choice'       => $options['create_search_choice'],
                'init_selection'             => $options['init_selection'],
                'select_query'               => $options['select_query'],
                'select_ajax'                => $options['select_ajax'],
                'select_data'                => $options['select_data'],
                'width'                      => $options['width'],
        ));

        if ($view->vars['ajax']) {
            $ajaxId = null !== $options['ajax_id'] ? $options['ajax_id'] : $view->vars['id'];
            $event = new GetAjaxChoiceListEvent($ajaxId, $this->request, $options['choice_list']);

            $this->dispatcher->dispatch(AjaxEvents::INJECTION, $event);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['form']->vars['no_label_for'] = true;

        $selected = (array) (is_string($view->vars['value']) ? explode(',', $view->vars['value']) : $view->vars['value']);

        if ($options['ajax']) {
            $view->vars['choices_selected'] = $options['choice_list']->getLabelChoicesForValues($selected);

            // convert array to string on error form validation
            if (is_array($view->vars['value'])) {
                $view->vars['value'] = implode(',', $view->vars['value']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'ajax'                       => false,
            'ajax_url'                   => null,
            'ajax_id'                    => null,
            'quiet_millis'               => 200,
            'page_size'                  => $this->ajaxPageSize,
            'width'                      => 'resolve',
            'close_on_select'            => null,
            'open_on_enter'              => null,
            'container_css'              => null,
            'dropdown_css'               => null,
            'container_css_class'        => null,
            'dropdown_css_class'         => null,
            'format_result'              => null,
            'format_selection'           => null,
            'format_result_css_class'    => null,
            'minimum_results_for_search' => null,
            'minimum_input_length'       => 0,
            'maximum_selection_size'     => null,
            'matcher'                    => null,
            'select_separator'           => null,
            'token_separators'           => null,
            'tokenizer'                  => null,
            'escape_markup'              => null,
            'blur_on_change'             => null,
            'placeholder'                => null,
            'format_no_matches'          => null,
            'format_input_too_short'     => null,
            'format_selection_too_big'   => null,
            'format_load_more'           => null,
            'format_searching'           => null,
            'select_id'                  => null,
            'create_search_choice'       => null,
            'init_selection'             => null,
            'select_query'               => null,
            'select_ajax'                => null,
            'select_data'                => null,
        ));

        $normalizers = array(
            'compound' => function (Options $options, $value) {
                if ($options['ajax'] || !$options['choice_list'] instanceof AjaxChoiceListInterface) {
                    return false;
                }

                return $value;
            },
        );

        if ($resolver->isKnown('expanded')) {
            $normalizers['expanded'] = function (Options $options, $value) {
                return false;
            };
        }

        if ($resolver->isKnown('choice_list')) {
            $normalizers['choice_list'] = function (Options $options, $value) {
                if (!$value instanceof AjaxChoiceListInterface) {
                    $value = new AjaxSimpleChoiceList($options['choices'], $options['preferred_choices']);
                }

                $value->setAjax($options['ajax']);
                $value->setPageSize($options['page_size']);
                $value->setPageNumber(1);
                $value->setSearch('');
                $value->setIds(array());

                return $value;
            };
        }

        $resolver->setNormalizers($normalizers);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->type . '_select2';
    }
}
