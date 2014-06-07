<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Controller;

use Sonatra\Bundle\FormExtensionsBundle\Controller\AjaxFormController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxFormControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AjaxFormController
     */
    protected $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helper;

    protected function setUp()
    {
        $this->controller = new AjaxFormController();
        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->helper = $this->getMockClass('Sonatra\Bundle\FormExtensionsBundle\Form\Helper\AjaxChoiceListHelper', array('generateResponse'));

        $ajaxChoiceList = $this->getMock('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface');

        $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $formBuilder->expects($this->any())
            ->method('getAttribute')
            ->with($this->equalTo('choice_list'))
            ->will($this->returnValue($ajaxChoiceList));

        $formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');
        $formFactory->expects($this->any())
            ->method('createBuilder')
            ->will($this->returnValue($formBuilder));

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->expects($this->any())
            ->method('get')
            ->will($this->returnValue($formFactory));

        /* @var ContainerInterface $container */
        $this->controller->setContainer($container);
    }

    protected function tearDown()
    {
        $this->controller = null;
        $this->helper = null;
        $this->request = null;
    }

    public function testAjaxChoiceListAction()
    {
        /* @var Request $request */
        $request = $this->request;

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent('{"length":null,"page_number":null,"page_size":null,"search":null,"results":null}');

        $this->assertEquals($response, $this->controller->ajaxChoiceListAction($request, 'locale'));
    }
}
