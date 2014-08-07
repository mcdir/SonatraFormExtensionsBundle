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

use Sonatra\Bundle\FormExtensionsBundle\Form\Extension\BaseChoiceSelect2TypeExtension;
use Sonatra\Bundle\FormExtensionsBundle\Form\Extension\ChoiceSelect2TypeExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Abstract tests case for base choice select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractBaseChoiceSelect2TypeExtensionTest extends TypeTestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

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

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $dispatcher = $this->dispatcher;
        $request = $this->request;
        $router = $this->router;

        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($param) use ($dispatcher, $request, $router) {
                switch ($param) {
                    case 'event_dispatcher':
                        return $dispatcher;
                    case 'request':
                        return $request;
                    case 'router':
                        return $router;
                    default:
                        return null;
                }
            }))
        ;

        /* @var ContainerInterface $container */
        $container = $this->container;

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(new ChoiceSelect2TypeExtension($container, $this->getExtensionTypeName(), 10))
            ->addTypeExtension(new BaseChoiceSelect2TypeExtension($this->getExtensionTypeName()))
            ->getFormFactory();

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->container = null;
        $this->request = null;
        $this->router = null;
    }

    /**
     * @return string
     */
    abstract protected function getExtensionTypeName();

    public function test()
    {
        //TODO
        $form = $this->factory->create($this->getExtensionTypeName());
        $config = $form->getConfig();

        $this->assertTrue($config->hasOption('select2'));
        $select2Opts = $config->getOption('select2');
        $this->assertTrue(array_key_exists('ajax_route', $select2Opts));
        $this->assertNull($select2Opts['ajax_route']);
        $this->assertEquals('sonatra_form_extensions_ajax_'.$this->getExtensionTypeName(), $config->getAttribute('select2_ajax_route'));
    }
}
