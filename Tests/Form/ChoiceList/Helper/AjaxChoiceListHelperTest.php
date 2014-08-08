<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Helper;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Form\Helper\AjaxChoiceListHelper;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxChoiceListHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var string
     */
    protected $helperClass = '\Sonatra\Bundle\FormExtensionsBundle\Form\Helper\AjaxChoiceListHelper';

    protected function setUp()
    {
        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->request->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($param) {
                $value = null;

                switch ($param) {
                    case 'ids':
                        $value = '1,2,3';
                        break;
                    case 'ps':
                        $value = '10';
                        break;
                    case 'pn':
                        $value = '1';
                        break;
                    case 's':
                        $value = 'search';
                        break;
                    default:
                        break;
                }

                return $value;
            }))
        ;
    }

    protected function tearDown()
    {
        $this->request = null;
    }

    public function testGetData()
    {
        /* @var Request $request */
        $request = $this->request;
        $formatter = new FixtureAjaxChoiceListFormatter();
        $choiceList = new AjaxSimpleChoiceList($formatter, array());
        $validData = array(
            'length'      => 0,
            'page_number' => 1,
            'page_size'   => 10,
            'search'      => 'search',
            'results'     => array(),
        );

        /* @var AjaxChoiceListHelper $helper */
        $helper = $this->helperClass;
        $this->assertEquals($validData, $helper::getData($request, $choiceList));
    }

    public function testGetDataWithoutIds()
    {
        $this->request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->request->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($param) {
                $value = null;

                switch ($param) {
                    case 'ids':
                        $value = null;
                        break;
                    case 'ps':
                        $value = '10';
                        break;
                    case 'pn':
                        $value = '1';
                        break;
                    case 's':
                        $value = 'search';
                        break;
                    default:
                        break;
                }

                return $value;
            }))
        ;

        $this->testGetData();
    }

    public function testGenerateJsonResponse()
    {
        /* @var AjaxChoiceListHelper $helper */
        $helper = $this->helperClass;
        /* @var Request $request */
        $request = $this->request;
        $formatter = new FixtureAjaxChoiceListFormatter();
        $choiceList = new AjaxSimpleChoiceList($formatter, array());
        $validData = '{"length":0,"page_number":1,"page_size":10,"search":"search","results":[]}';
        $response = $helper::generateResponse($request, $choiceList, 'json');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($validData, $response->getContent());
    }

    public function testGenerateXmlResponse()
    {
        /* @var AjaxChoiceListHelper $helper */
        $helper = $this->helperClass;
        /* @var Request $request */
        $request = $this->request;
        $formatter = new FixtureAjaxChoiceListFormatter();
        $choiceList = new AjaxSimpleChoiceList($formatter, array());
        $validData = "<?xml version=\"1.0\"?>\n<response><length>0</length><page_number>1</page_number><page_size>10</page_size><search>search</search><results/></response>\n";
        $response = $helper::generateResponse($request, $choiceList, 'xml');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($validData, $response->getContent());
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function testGenerateResponseWithInvalidFormat()
    {
        /* @var AjaxChoiceListHelper $helper */
        $helper = $this->helperClass;
        /* @var Request $request */
        $request = $this->request;

        $helper::generateResponse($request, null, 'invalid');
    }

    public function testGenerateJsonResponseWithForm()
    {
        /* @var AjaxChoiceListHelper $helper */
        $helper = $this->helperClass;
        /* @var Request $request */
        $request = $this->request;
        $formatter = new FixtureAjaxChoiceListFormatter();
        $choiceList = new AjaxSimpleChoiceList($formatter, array());
        $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $formBuilder->expects($this->any())
            ->method('getAttribute')
            ->with($this->equalTo('choice_list'))
            ->will($this->returnValue($choiceList));

        $validData = '{"length":0,"page_number":1,"page_size":10,"search":"search","results":[]}';
        $response = $helper::generateResponse($request, $formBuilder, 'json');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($validData, $response->getContent());
    }

    public function testGenerateJsonResponseWithFormBuilder()
    {
        /* @var AjaxChoiceListHelper $helper */
        $helper = $this->helperClass;
        /* @var Request $request */
        $request = $this->request;
        $formatter = new FixtureAjaxChoiceListFormatter();
        $choiceList = new AjaxSimpleChoiceList($formatter, array());
        $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $formBuilder->expects($this->any())
            ->method('getAttribute')
            ->with($this->equalTo('choice_list'))
            ->will($this->returnValue($choiceList));
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($formBuilder));

        $validData = '{"length":0,"page_number":1,"page_size":10,"search":"search","results":[]}';

        $response = $helper::generateResponse($request, $form, 'json');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($validData, $response->getContent());
    }

    public function testGenerateJsonResponseWithArray()
    {
        $this->setExpectedException('Symfony\Component\Form\Exception\InvalidArgumentException');

        /* @var AjaxChoiceListHelper $helper */
        $helper = $this->helperClass;
        /* @var Request $request */
        $request = $this->request;

        $helper::generateResponse($request, array(), 'json');
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\InvalidArgumentException
     */
    public function testGenerateJsonResponseWithInvalidChoiceList()
    {
        /* @var AjaxChoiceListHelper $helper */
        $helper = $this->helperClass;
        /* @var Request $request */
        $request = $this->request;

        $helper::generateResponse($request, null, 'json');
    }
}
