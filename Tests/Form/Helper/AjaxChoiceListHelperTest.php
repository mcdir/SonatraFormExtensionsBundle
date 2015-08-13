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

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\Helper\AjaxChoiceListHelper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Tests case for choice list helper.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxChoiceListHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return string
     */
    protected function getHelperClass()
    {
        return 'Sonatra\Bundle\FormExtensionsBundle\Form\Helper\AjaxChoiceListHelper';
    }

    public function testInvalidFormat()
    {
        $msg = 'The \'invalid\' format is not allowed. Try with \'xml\', \'json\'';
        $this->setExpectedException('Symfony\Component\Form\Exception\InvalidArgumentException', $msg);

        /* @var Request $request */
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        /* @var AjaxChoiceLoaderInterface $choiceLoader */
        $choiceLoader = $this->getMock('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface');

        $helper = $this->getHelperClass();
        /* @var AjaxChoiceListHelper $helper */
        $helper::generateResponse($request, $choiceLoader, 'invalid');
    }

    public function testInvalidFormatter()
    {
        if ('Sonatra\Bundle\FormExtensionsBundle\Form\Helper\AjaxChoiceListHelper' !== $this->getHelperClass()) {
            return;
        }

        $msg = 'You must create a child class of AjaxChoiceLostHelper and override the "createChoiceListFormatter" method';
        $this->setExpectedException('Symfony\Component\Form\Exception\InvalidArgumentException', $msg);

        /* @var Request $request */
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        /* @var AjaxChoiceLoaderInterface $choiceLoader */
        $choiceLoader = $this->getMock('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface');

        $helper = $this->getHelperClass();
        /* @var AjaxChoiceListHelper $helper */
        $helper::generateResponse($request, $choiceLoader);
    }

    public function testInvalidFormAjaxFormatter()
    {
        $msg = 'Expected argument of type "Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface", "NULL" given';
        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException', $msg);

        /* @var Request $request */
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');

        /* @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form */
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($formBuilder));

        $helper = $this->getHelperClass();
        /* @var AjaxChoiceListHelper $helper */
        $helper::generateResponse($request, $form);
    }

    public function testInvalidChoiceLoader()
    {
        $msg = 'Expected argument of type "Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface", "NULL" given';
        $this->setExpectedException('Symfony\Component\Form\Exception\UnexpectedTypeException', $msg);

        /* @var Request $request */
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $formatter = $this->getMock('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface');

        $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $formBuilder->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnCallback(function ($value) use ($formatter) {
                return 'select2' === $value
                    ? array(
                        'ajax_formatter' => $formatter,
                    )
                    : null;
            }));

        /* @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form */
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($formBuilder));

        $helper = $this->getHelperClass();
        /* @var AjaxChoiceListHelper $helper */
        $helper::generateResponse($request, $form);
    }

    public function getAjaxIds()
    {
        return array(
            array(null),
            array(''),
            array('1'),
            array('1,2'),
            array(array(1, 2)),
        );
    }

    /**
     * @dataProvider getAjaxIds
     *
     * @param null|string|array $ajaxIds
     */
    public function testGenerateResponse($ajaxIds)
    {
        /* @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($value) use ($ajaxIds) {
                return 'prefix_ids' === $value
                    ? $ajaxIds
                    : null;
            }));

        $formatter = $this->getMock('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface');
        $formatter->expects($this->any())
            ->method('formatResponseData')
            ->will($this->returnValue('MOCK_FORMATTED_DATA'));

        $choiceLoader = $this->getMock('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface');

        $formBuilder = $this->getMock('Symfony\Component\Form\FormBuilderInterface');
        $formBuilder->expects($this->any())
            ->method('getAttribute')
            ->will($this->returnCallback(function ($value) use ($formatter, $choiceLoader) {
                if ('select2' === $value) {
                    return array(
                        'ajax_formatter' => $formatter,
                    );
                } elseif ('choice_loader' === $value) {
                    return $choiceLoader;
                }

                return $value;
            }));

        /* @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form */
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $form->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($formBuilder));

        $helper = $this->getHelperClass();
        /* @var AjaxChoiceListHelper $helper */
        $res = $helper::generateResponse($request, $form, 'json', 'prefix_');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $res);
        $this->assertSame('application/json', $res->headers->get('Content-Type'));
        $this->assertSame(json_encode('MOCK_FORMATTED_DATA'), $res->getContent());
    }

    /**
     * @param null|string|array $ajaxIds
     * @param array             $validContent
     */
    protected function executeGenerateResponseWithCreateFormatter($ajaxIds, array $validContent)
    {
        /* @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $request->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($value) use ($ajaxIds) {
                return 'prefix_ids' === $value
                    ? $ajaxIds
                    : null;
            }));

        $choiceList = $this->getMock('Symfony\Component\Form\ChoiceList\ChoiceListInterface');
        $choiceList->expects($this->any())
            ->method('getChoices')
            ->will($this->returnValue(array()));
        $choiceList->expects($this->any())
            ->method('getOriginalKeys')
            ->will($this->returnValue(array()));
        $choiceList->expects($this->any())
            ->method('getStructuredValues')
            ->will($this->returnValue(array()));

        /* @var AjaxChoiceLoaderInterface|\PHPUnit_Framework_MockObject_MockObject $choiceLoader */
        $choiceLoader = $this->getMock('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface');
        $choiceLoader->expects($this->any())
            ->method('loadPaginatedChoiceList')
            ->will($this->returnValue($choiceList));

        $helper = $this->getHelperClass();
        /* @var AjaxChoiceListHelper $helper */
        $res = $helper::generateResponse($request, $choiceLoader, 'json', 'prefix_');
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $res);
        $this->assertSame('application/json', $res->headers->get('Content-Type'));
        $this->assertEquals($validContent, json_decode($res->getContent(), true));
    }
}
