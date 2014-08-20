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

use Sonatra\Bundle\AjaxBundle\AjaxEvents;
use Sonatra\Bundle\FormExtensionsBundle\Event\GetAjaxChoiceListEvent;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\Select2AjaxChoiceListFormatter;
use Sonatra\Bundle\FormExtensionsBundle\Form\EventListener\FixStringInputSubscriber;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\ChoicesToValuesTransformer;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\ChoiceToValueTransformer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractSelect2TypeExtension extends AbstractTypeExtension
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var integer
     */
    protected $ajaxPageSize;

    /**
     * Constructor.
     *
     * @param EventDispatcherInterface $dispatcher
     * @param RequestStack             $requestStack
     * @param RouterInterface          $router
     * @param string                   $type
     * @param integer                  $defaultPageSize
     */
    public function __construct(EventDispatcherInterface $dispatcher, RequestStack $requestStack, RouterInterface $router, $type, $defaultPageSize = 10)
    {
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->type = $type;
        $this->ajaxPageSize = $defaultPageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['select2']['enabled']) {
            return;
        }

        $builder->resetViewTransformers();

        if ($options['multiple']) {
            $builder->addViewTransformer(new ChoicesToValuesTransformer($options['choice_list'], $options['required']));

        } else {
            $builder->addViewTransformer(new ChoiceToValueTransformer($options['choice_list'], $options['required']));
        }

        if ($options['select2']['ajax'] && $options['multiple']) {
            $builder->addEventSubscriber(new FixStringInputSubscriber());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!$options['select2']['enabled']) {
            return;
        }

        list($ajaxUrl, $routeName) = $this->getAjaxUrlAndRouteName($form, $options);
        $choiceList = $this->getAjaxChoiceList($form, $options);

        if ($options['select2']['ajax'] && null === $routeName) {
            $event = new GetAjaxChoiceListEvent($view->vars['id'], $this->requestStack, $choiceList);
            $this->dispatcher->dispatch(AjaxEvents::INJECTION, $event);
        }

        $view->vars = array_replace($view->vars,
            $this->getReplaceViewVars($view, $options, $ajaxUrl, $routeName));

        if ($options['select2']['ajax'] && !$options['multiple'] && !$options['required']) {
            $view->vars['attr']['placeholder'] = ' ';
        }

        if (is_array($options['select2']['tags'])) {
            $view->vars['select2']['tags'] = $options['select2']['tags'];
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

        $this->addChoicesSelectedInView($view, $options);

        // convert array to string
        if ($options['select2']['ajax'] && is_array($view->vars['value'])) {
            $view->vars['value'] = implode(',', $view->vars['value']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'select2' => array(),
        ));

        $resolver->setAllowedTypes(array(
            'select2' => 'array',
        ));

        $ajaxPageSize = $this->ajaxPageSize;
        $normalizers = array(
            'select2' => function (Options $options, $value) use ($ajaxPageSize) {
                $select2Resolver = new OptionsResolver();
                $pDefault = $options;
                $enabled = function (Options $options) use ($pDefault) {
                    $options->valid();

                    return !$pDefault['expanded'];
                };

                $select2Resolver->setDefaults(array(
                    'enabled'                    => $enabled,
                    'wrapper_attr'               => array(),
                    'formatter'                  => new Select2AjaxChoiceListFormatter(),
                    'allow_add'                  => false,
                    'ajax'                       => false,
                    'ajax_route'                 => null,
                    'ajax_parameters'            => array(),
                    'ajax_reference_type'        => RouterInterface::ABSOLUTE_PATH,
                    'quiet_millis'               => 200,
                    'page_size'                  => $ajaxPageSize,
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
                    'select_id'                  => null,
                    'create_search_choice'       => null,
                    'init_selection'             => null,
                    'select_query'               => null,
                    'select_ajax'                => null,
                    'select_data'                => null,
                    'tags'                       => null,
                ));

                $select2Resolver->setAllowedTypes(array(
                    'enabled'             => 'bool',
                    'wrapper_attr'        => 'array',
                    'formatter'           => 'Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface',
                    'allow_add'           => 'bool',
                    'ajax'                => 'bool',
                    'ajax_route'          => array('null', 'string'),
                    'ajax_parameters'     => 'array',
                    'ajax_reference_type' => 'bool',
                    'tags'                => array('null', 'array'),
                ));

                $select2Resolver->setNormalizers(array(
                    'allow_add'  => function (Options $options, $value) {
                        if (null !== $options['tags']) {
                            return true;
                        }

                        return $value;
                    },
                ));

                return $select2Resolver->resolve($value);
            },
            'compound' => function (Options $options) {
                if ($options['select2']['enabled'] && ($options['select2']['ajax'] || !$options['choice_list'] instanceof AjaxChoiceListInterface)) {
                    return false;
                }

                return $options['expanded'];
            },
        );

        if ($resolver->isKnown('expanded')) {
            $normalizers['expanded'] = function (Options $options, $value) {
                return $value;
            };
        }

        if ($resolver->isKnown('choice_list')) {
            $normalizers['choice_list'] = function (Options $options, $value) {
                if ($options['select2']['enabled']) {
                    if (!$value instanceof AjaxChoiceListInterface) {
                        $value = new AjaxSimpleChoiceList($options['select2']['formatter'], $options['choices'], $options['preferred_choices']);
                    }

                    $value->setAllowAdd($options['select2']['allow_add']);
                    $value->setPageSize($options['select2']['page_size']);
                    $value->setPageNumber(1);
                    $value->setSearch('');
                    $value->setIds(array());
                    $value->reset();
                }

                return $value;
            };
        }

        $resolver->setNormalizers($normalizers);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return $this->type;
    }

    /**
     * Get ajax choice list.
     *
     * @param FormInterface $form
     * @param array         $options
     *
     * @return AjaxChoiceListInterface
     */
    protected function getAjaxChoiceList(FormInterface $form, array $options)
    {
        $choiceList = $form->getConfig()->getAttribute('choice_list');

        if (isset($options['choice_list'])) {
            $choiceList = $options['choice_list'];
        }

        return $choiceList;
    }

    /**
     * Gets the ajax url and route name.
     *
     * @param FormInterface $form
     * @param array         $options
     *
     * @return array The ajaxUrl and routeName
     */
    protected function getAjaxUrlAndRouteName(FormInterface $form, array $options)
    {
        $ajaxUrl = $this->requestStack->getMasterRequest()->getRequestUri();
        $routeName = null;

        if ($options['select2']['ajax']) {
            $routeName = $form->getConfig()->getAttribute('select2_ajax_route', $options['select2']['ajax_route']);

            if (null !== $routeName) {
                $routeParams = $options['select2']['ajax_parameters'];
                $routeReferenceType = $options['select2']['ajax_reference_type'];
                $ajaxUrl = $this->router->generate($routeName, $routeParams, $routeReferenceType);
            }
        }

        return array($ajaxUrl, $routeName);
    }

    /**
     * Gets the new view vars for the replacement.
     *
     * @param FormView    $view
     * @param array       $options
     * @param string      $ajaxUrl
     * @param string|null $routeName
     *
     * @return array
     */
    protected function getReplaceViewVars(FormView $view, array $options, $ajaxUrl, $routeName)
    {
        return array(
            'select2'  => array(
                'wrapper_attr'               => $options['select2']['wrapper_attr'],
                'allow_clear'                => $options['required'] ? 'false' : 'true',
                'ajax'                       => $options['select2']['ajax'],
                'ajax_url'                   => $ajaxUrl,
                'ajax_id'                    => (null === $routeName && isset($choiceList)) ? $view->vars['id'] : null,
                'quiet_millis'               => $options['select2']['quiet_millis'],
                'page_size'                  => $options['select2']['page_size'],
                'close_on_select'            => $options['select2']['close_on_select'],
                'open_on_enter'              => $options['select2']['open_on_enter'],
                'container_css'              => $options['select2']['container_css'],
                'dropdown_css'               => $options['select2']['dropdown_css'],
                'container_css_class'        => $options['select2']['container_css_class'],
                'dropdown_css_class'         => $options['select2']['dropdown_css_class'],
                'format_result'              => $options['select2']['format_result'],
                'format_selection'           => $options['select2']['format_selection'],
                'format_result_css_class'    => $options['select2']['format_result_css_class'],
                'minimum_results_for_search' => $options['select2']['minimum_results_for_search'],
                'minimum_input_length'       => $options['select2']['minimum_input_length'],
                'maximum_selection_size'     => $options['select2']['maximum_selection_size'],
                'matcher'                    => $options['select2']['matcher'],
                'select_separator'           => $options['select2']['select_separator'],
                'token_separators'           => $options['select2']['token_separators'],
                'tokenizer'                  => $options['select2']['tokenizer'],
                'escape_markup'              => $options['select2']['escape_markup'],
                'blur_on_change'             => $options['select2']['blur_on_change'],
                'select_id'                  => $options['select2']['select_id'],
                'create_search_choice'       => $options['select2']['create_search_choice'],
                'init_selection'             => $options['select2']['init_selection'],
                'select_query'               => $options['select2']['select_query'],
                'select_ajax'                => $options['select2']['select_ajax'],
                'select_data'                => $options['select2']['select_data'],
                'width'                      => $options['select2']['width'],
            ),
            'required' => $options['select2']['ajax'] ? false : $options['required'],
        );
    }

    /**
     * Adds formatted choices selected in form view.
     *
     * @param FormView $view
     * @param array    $options
     *
     * @return void
     */
    protected function addChoicesSelectedInView(FormView $view, array $options)
    {
        if ($options['select2']['ajax'] && isset($options['choice_list'])) {
            /* @var AjaxChoiceListInterface $choiceList */
            $choiceList = $options['choice_list'];
            $values = (array) $view->vars['value'];

            // add first value
            if ($options['required'] && null === $view->vars['data']) {
                $firstChoice = $choiceList->getFirstChoiceView();

                if (null !== $firstChoice) {
                    $values = (array) $firstChoice->value;
                }
            }

            $view->vars['choices_selected'] = $choiceList->getFormattedChoicesForValues($values);
        }
    }
}
