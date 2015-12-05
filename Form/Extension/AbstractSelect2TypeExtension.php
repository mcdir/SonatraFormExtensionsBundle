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
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\DynamicChoiceLoaderInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\Select2ChoiceToValueTransformer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceListView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractSelect2TypeExtension extends AbstractSelect2ConfigTypeExtension
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
     * Constructor.
     *
     * @param EventDispatcherInterface   $dispatcher
     * @param RequestStack               $requestStack
     * @param RouterInterface            $router
     * @param string                     $type
     * @param int                        $defaultPageSize
     * @param ChoiceListFactoryInterface $choiceListFactory
     */
    public function __construct(EventDispatcherInterface $dispatcher, RequestStack $requestStack, RouterInterface $router, $type, $defaultPageSize = 10, ChoiceListFactoryInterface $choiceListFactory = null)
    {
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->type = $type;

        parent::__construct($defaultPageSize, $choiceListFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['select2']['enabled'] || $options['multiple'] || !$options['select2']['tags']) {
            return;
        }

        $builder->resetViewTransformers();
        $builder->addViewTransformer(new Select2ChoiceToValueTransformer($options['choice_loader']));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!$options['select2']['enabled']) {
            return;
        }

        $this->prepareView($view, $form, $options);

        list($ajaxUrl, $routeName) = $this->getAjaxUrlAndRouteName($form, $options);
        $choiceLoader = $this->getChoiceLoader($form, $options);

        if ($options['select2']['ajax'] && $choiceLoader instanceof AjaxChoiceLoaderInterface
                && null === $routeName && null !== $choiceLoader) {
            $event = new GetAjaxChoiceListEvent($view->vars['id'], $this->requestStack, $choiceLoader, $options['select2']['ajax_formatter']);
            $this->dispatcher->dispatch(AjaxEvents::INJECTION, $event);
        }

        $view->vars = array_replace($view->vars,
            $this->getReplaceViewVars($view, $options, $ajaxUrl, $routeName));
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
        $view->vars['required'] = !isset($options['multiple']) || $options['multiple'] ? false : $view->vars['required'];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return $this->type;
    }

    /**
     * Prepare the view.
     *
     * @param FormView      $view    The view
     * @param FormInterface $form    The form
     * @param array         $options The form options
     */
    protected function prepareView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($options['choice_loader'])
            && $options['choice_loader'] instanceof DynamicChoiceLoaderInterface) {
            /* @var DynamicChoiceLoaderInterface $loader */
            $loader = $options['choice_loader'];
            $values = is_object($form->getData()) ? array($form->getData()) : (array) $form->getData();
            $choiceListView = $this->createChoiceListView($loader->loadChoiceListForView($values, $options['choice_name']), $options);

            $view->vars = array_replace($view->vars, array(
                'preferred_choices' => $choiceListView->preferredChoices,
                'choices' => $choiceListView->choices,
            ));
        }
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
     * Get choice loader.
     *
     * @param FormInterface $form
     * @param array         $options
     *
     * @return ChoiceLoaderInterface
     */
    protected function getChoiceLoader(FormInterface $form, array $options)
    {
        $choiceLoader = $form->getConfig()->getAttribute('choice_loader');

        if (isset($options['choice_loader'])) {
            $choiceLoader = $options['choice_loader'];
        }

        return $choiceLoader;
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
            'select2' => $this->skipNullValue(array(
                'wrapper_attr' => $options['select2']['wrapper_attr'],
                'placeholder' => isset($options['placeholder']) ? $options['placeholder'] : null,
                'width' => $options['select2']['width'],
                'allow_clear' => $options['required'] ? null : 'true',
                'template_result' => $options['select2']['template_result'],
                'template_selection' => $options['select2']['template_selection'],
                'dropdown_parent' => $options['select2']['dropdown_parent'],
                'selection_adapter' => $options['select2']['selection_adapter'],
                'data_adapter' => $options['select2']['data_adapter'],
                'dropdown_adapter' => $options['select2']['dropdown_adapter'],
                'results_adapter' => $options['select2']['results_adapter'],
                'matcher' => $options['select2']['matcher'],
                'create_tag' => $options['select2']['create_tag'],
                'close_on_select' => $options['select2']['close_on_select'],
                'min_results_for_search' => $options['select2']['min_results_for_search'],
                'min_input_length' => $options['select2']['min_input_length'],
                'max_input_length' => $options['select2']['max_input_length'],
                'data' => $options['select2']['data'],
                'tags' => $options['select2']['tags'] ? 'true' : null,
                'token_separators' => $options['select2']['token_separators'],
                'dir' => $options['select2']['dir'],
                'theme' => $options['select2']['theme'],
                'language' => strtolower(str_replace('_', '-', $options['select2']['language'])),
                'ajax' => array(
                    'enabled' => $options['select2']['ajax'],
                    'url' => $ajaxUrl,
                    'data_type' => $options['select2']['ajax_data_type'],
                    'delay' => $options['select2']['ajax_delay'],
                    'cache' => $options['select2']['ajax_cache'] ? 'true' : null,
                    'ajax_id' => null === $routeName ? $view->vars['id'] : null,
                    'page_size' => $options['select2']['ajax_page_size'],
                ),
            )),
        );
    }

    /**
     * Remove the key with null values.
     *
     * @param array $attributes The view attributes
     *
     * @return array The view attributes without null values
     */
    protected function skipNullValue(array $attributes)
    {
        $attr = array();

        foreach ($attributes as $key => $value) {
            if (null !== $value) {
                $attr[$key] = is_array($value)
                    ? $this->skipNullValue($value)
                    : $value;
            }
        }

        return $attr;
    }

    /**
     * Create a choice list view.
     *
     * @param ChoiceListInterface $choiceList The choice list
     * @param array               $options    The form options
     *
     * @return ChoiceListView
     */
    private function createChoiceListView(ChoiceListInterface $choiceList, array $options)
    {
        return $this->choiceListFactory->createView(
            $choiceList,
            $options['preferred_choices'],
            $options['choice_label'],
            $options['choice_name'],
            $options['group_by'],
            $options['choice_attr']
        );
    }
}
