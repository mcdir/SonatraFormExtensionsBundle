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
use Sonatra\Bundle\FormExtensionsBundle\Event\GetAjaxSelect2Event;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Select2ChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Select2EntityChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\ArrayToStringTransformer;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\StringToStringTransformer;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Select2Type extends AbstractType
{
    /**
     * @var string
     */
    private $typeName;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var integer
     */
    private $ajaxPageSize;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param string             $typeName
     * @param integer            $defaultPageSize
     */
    public function __construct(ContainerInterface $container, $typeName = 'entity', $defaultPageSize = 10)
    {
        $this->typeName = $typeName;
        $this->dispatcher = $container->get('event_dispatcher');
        $this->request = $container->get('request');
        $this->ajaxPageSize = $defaultPageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['ajax'] || null !== $options['tags']) {
            if ($options['multiple']) {
                $builder->addViewTransformer(new ArrayToStringTransformer($options['required'], $options['compound']));
            } else {
                $builder->addViewTransformer(new StringToStringTransformer($options['required']));
            }
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
                'tags'                       => $options['tags'],
                'width'                      => $options['width'],
        ));

        if (!($options['choice_list'] instanceof Select2ChoiceListInterface)) {
            $view->vars['ajax'] = false;
        }

        if ($view->vars['ajax']) {
            $ajaxId = null !== $options['ajax_id'] ? $options['ajax_id'] : $view->vars['id'];
            $eOptions = array_replace($options, array('ajaxPageSize' => $this->ajaxPageSize));
            $event = new GetAjaxSelect2Event($ajaxId, $this->request, $eOptions);

            $this->dispatcher->dispatch(AjaxEvents::INJECTION, $event);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['form']->vars['no_label_for'] = true;
        $selected = $view->vars['value'];

        if (is_string($selected)) {
            $selected = explode(',', $view->vars['value']);
        }

        if ($view->vars['ajax']) {
            $view->vars['choices_selected'] = $options['choice_list']->getIntersect($selected);

        } else {
            $view->vars['choices_selected'] = $options['choice_list']->getChoices();
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
            'quiet_millis'               => 100,
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
            'multiple'                   => null,
            'select_id'                  => null,
            'create_search_choice'       => null,
            'init_selection'             => null,
            'select_query'               => null,
            'select_ajax'                => null,
            'select_data'                => null,
            'tags'                       => null,
            'choice_list'                => function (Options $options, $previousValue) {
                if ('entity' === $this->typeName) {
                    return new Select2EntityChoiceList(
                        $options['em'],
                        $options['class'],
                        $options['property'],
                        $options['query_builder'],
                        $options['choices'],
                        $options['group_by'],
                        $options['ajax'],
                        '',
                        1,
                        $this->ajaxPageSize,
                        array()
                    );
                }

                return $previousValue;
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->typeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->typeName . '_select2';
    }
}
