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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * Abstract tests case for base choice select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractBaseChoiceSelect2TypeExtensionTest extends TypeTestCase
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var RouterInterface
     */
    protected $router;

    protected function setUp()
    {
        parent::setUp();

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->router = $this->getMock('Symfony\Component\Routing\RouterInterface');
        $router = $this->router;
        $this->requestStack = new RequestStack();
        /* @var Request $request */
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->requestStack->push($request);

        /* @var EventDispatcherInterface $dispatcher */
        $dispatcher = $this->dispatcher;
        /* @var RouterInterface $router */

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(new ChoiceSelect2TypeExtension($dispatcher, $this->requestStack, $router, $this->getExtensionTypeName(), 10))
            ->addTypeExtension(new BaseChoiceSelect2TypeExtension($this->getExtensionTypeName()))
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
