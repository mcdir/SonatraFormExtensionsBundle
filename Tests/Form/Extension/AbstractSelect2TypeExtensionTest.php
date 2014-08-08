<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\Extension;

use Sonatra\Bundle\FormExtensionsBundle\Form\Extension\ChoiceSelect2TypeExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Tests case for abstract select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractSelect2TypeExtensionTest extends TypeTestCase
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var RouterInterface
     */
    protected $router;

    protected function setUp()
    {
        parent::setUp();

        \Locale::setDefault('en');

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $this->router->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback(function ($param) {
                return '/' . $param;
            }))
        ;

        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        /* @var Request $request */
        $request = $this->request;
        /* @var RouterInterface $router */
        $router = $this->router;

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(new ChoiceSelect2TypeExtension($dispatcher, $request, $router, $this->getExtensionTypeName(), 10))
            ->getFormFactory();

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->request = null;
        $this->router = null;
    }

    /**
     * @return array|null
     */
    protected function getChoices()
    {
        return null;
    }

    protected function mergeOptions(array $options)
    {
        if (is_array($this->getChoices())) {
            $options['choices'] = $this->getChoices();
        }

        return $options;
    }

    /**
     * @return string
     */
    abstract protected function getExtensionTypeName();

    /**
     * @return string
     */
    abstract protected function getSingleData();

    /**
     * @return string
     */
    abstract protected function getValidSingleValue();

    /**
     * @return string
     */
    abstract protected function getValidAjaxSingleValue();

    /**
     * @return array
     */
    abstract protected function getMultipleData();

    /**
     * @return array
     */
    abstract protected function getValidMultipleValue();

    /**
     * @return string
     */
    abstract protected function getValidAjaxMultipleValue();

    /**
     * @return array
     */
    abstract protected function getValidFirstChoiceSelected();

    public function testDefaultOptions()
    {
        $form = $this->factory->create($this->getExtensionTypeName(), $this->getSingleData(), $this->mergeOptions(array()));
        $config = $form->getConfig();

        $this->assertFalse($config->getOption('compound'));
        $this->assertFalse($config->getOption('multiple'));
        $this->assertTrue($config->hasOption('select2'));
        $select2Opts = $config->getOption('select2');
        $this->assertTrue($select2Opts['enabled']);
        $this->assertFalse($select2Opts['ajax']);
        $this->assertFalse($select2Opts['allow_add']);
        $this->assertInstanceOf('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface', $config->getOption('choice_list'));

        $view = $form->createView();

        $this->assertTrue(array_key_exists('select2', $view->vars));
        $this->assertEquals($this->getSingleData(), $view->vars['data']);
        $this->assertEquals($this->getValidSingleValue(), $view->vars['value']);
    }

    public function testDisabled()
    {
        $options = array('select2' => array('enabled' => false));
        $form = $this->factory->create($this->getExtensionTypeName(), $this->getSingleData(), $this->mergeOptions($options));
        $config = $form->getConfig();

        $this->assertFalse($config->getOption('compound'));
        $this->assertFalse($config->getOption('multiple'));
        $this->assertTrue($config->hasOption('select2'));
        $select2Opts = $config->getOption('select2');
        $this->assertFalse($select2Opts['enabled']);

        $view = $form->createView();
        $this->assertFalse(array_key_exists('select2', $view->vars));
        $this->assertEquals($this->getSingleData(), $view->vars['data']);
        $this->assertEquals($this->getValidSingleValue(), $view->vars['value']);
    }

    public function testSingleWithTags()
    {
        $options = array('select2' => array('tags' => array()));
        $form = $this->factory->create($this->getExtensionTypeName(), $this->getSingleData(), $this->mergeOptions($options));
        $config = $form->getConfig();

        $this->assertFalse($config->getOption('compound'));
        $this->assertFalse($config->getOption('multiple'));
        $this->assertTrue($config->hasOption('select2'));
        $select2Opts = $config->getOption('select2');
        $this->assertTrue($select2Opts['enabled']);
        $this->assertFalse($select2Opts['ajax']);
        $this->assertTrue($select2Opts['allow_add']);
        $this->assertInstanceOf('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface', $config->getOption('choice_list'));

        $view = $form->createView();

        $this->assertTrue(array_key_exists('select2', $view->vars));
        $this->assertEquals($this->getSingleData(), $view->vars['data']);
        $this->assertEquals($this->getValidSingleValue(), $view->vars['value']);
        $this->assertTrue(array_key_exists('tags', $view->vars['select2']));
    }

    public function testSingleAjax()
    {
        $options = array('select2' => array('ajax' => true));
        $form = $this->factory->create($this->getExtensionTypeName(), $this->getSingleData(), $this->mergeOptions($options));
        $config = $form->getConfig();

        $this->assertFalse($config->getOption('compound'));
        $this->assertFalse($config->getOption('multiple'));
        $this->assertTrue($config->hasOption('select2'));
        $select2Opts = $config->getOption('select2');
        $this->assertTrue($select2Opts['enabled']);
        $this->assertTrue($select2Opts['ajax']);
        $this->assertFalse($select2Opts['allow_add']);
        $this->assertInstanceOf('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface', $config->getOption('choice_list'));

        $view = $form->createView();

        $this->assertTrue(array_key_exists('select2', $view->vars));
        $this->assertEquals($this->getSingleData(), $view->vars['data']);
        $this->assertEquals($this->getValidAjaxSingleValue(), $view->vars['value']);
    }

    public function testSingleAjaxWithTags()
    {
        $options = array('select2' => array('ajax' => true, 'tags' => array()));
        $form = $this->factory->create($this->getExtensionTypeName(), $this->getSingleData(), $this->mergeOptions($options));
        $config = $form->getConfig();

        $this->assertFalse($config->getOption('compound'));
        $this->assertFalse($config->getOption('multiple'));
        $this->assertTrue($config->hasOption('select2'));
        $select2Opts = $config->getOption('select2');
        $this->assertTrue($select2Opts['enabled']);
        $this->assertTrue($select2Opts['ajax']);
        $this->assertTrue($select2Opts['allow_add']);
        $this->assertInstanceOf('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface', $config->getOption('choice_list'));

        $view = $form->createView();

        $this->assertTrue(array_key_exists('select2', $view->vars));
        $this->assertEquals($this->getSingleData(), $view->vars['data']);
        $this->assertEquals($this->getValidAjaxSingleValue(), $view->vars['value']);
    }

    public function testMultiple()
    {
        $options = array('multiple' => true);
        $form = $this->factory->create($this->getExtensionTypeName(), $this->getMultipleData(), $this->mergeOptions($options));
        $config = $form->getConfig();

        $this->assertFalse($config->getOption('compound'));
        $this->assertTrue($config->getOption('multiple'));
        $this->assertTrue($config->hasOption('select2'));
        $select2Opts = $config->getOption('select2');
        $this->assertTrue($select2Opts['enabled']);
        $this->assertFalse($select2Opts['ajax']);
        $this->assertFalse($select2Opts['allow_add']);
        $this->assertInstanceOf('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface', $config->getOption('choice_list'));

        $view = $form->createView();

        $this->assertTrue(array_key_exists('select2', $view->vars));
        $this->assertEquals($this->getMultipleData(), $view->vars['data']);
        $this->assertEquals($this->getValidMultipleValue(), $view->vars['value']);
    }

    public function testMultipleAjax()
    {
        $options = array('multiple' => true, 'select2' => array('ajax' => true));
        $form = $this->factory->create($this->getExtensionTypeName(), $this->getMultipleData(), $this->mergeOptions($options));
        $config = $form->getConfig();

        $this->assertFalse($config->getOption('compound'));
        $this->assertTrue($config->getOption('multiple'));
        $this->assertTrue($config->hasOption('select2'));
        $select2Opts = $config->getOption('select2');
        $this->assertTrue($select2Opts['enabled']);
        $this->assertTrue($select2Opts['ajax']);
        $this->assertFalse($select2Opts['allow_add']);
        $this->assertInstanceOf('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface', $config->getOption('choice_list'));

        $view = $form->createView();

        $this->assertTrue(array_key_exists('select2', $view->vars));
        $this->assertEquals($this->getMultipleData(), $view->vars['data']);
        $this->assertEquals($this->getValidAjaxMultipleValue(), $view->vars['value']);
    }

    public function testRequiredAjaxFirstChoice()
    {
        $options = array('select2' => array('ajax' => true));
        $form = $this->factory->create($this->getExtensionTypeName(), null, $this->mergeOptions($options));
        $view = $form->createView();

        $this->assertEquals(array($this->getValidFirstChoiceSelected()), $view->vars['choices_selected']);
    }

    public function testSinglePlaceHolder()
    {
        $options = array('required' => false, 'select2' => array('ajax' => true));
        $form = $this->factory->create($this->getExtensionTypeName(), null, $this->mergeOptions($options));
        $view = $form->createView();

        $this->assertTrue(isset($view->vars['attr']['placeholder']));
        $this->assertEquals(' ', $view->vars['attr']['placeholder']);
    }

    public function testAjaxRoute()
    {
        $options = array('required' => false, 'select2' => array('ajax' => true, 'ajax_route' => 'foobar'));
        $form = $this->factory->create($this->getExtensionTypeName(), null, $this->mergeOptions($options));
        $view = $form->createView();

        $this->assertEquals('/foobar', $view->vars['select2']['ajax_url']);
    }
}
