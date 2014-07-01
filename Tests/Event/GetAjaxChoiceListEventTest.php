<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Event;

use Sonatra\Bundle\FormExtensionsBundle\Event\GetAjaxChoiceListEvent;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GetAjaxChoiceListEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GetAjaxChoiceListEvent
     */
    protected $event;

    protected function setUp()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $choiceList = $this->getMock('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface');

        $this->event = new GetAjaxChoiceListEvent('foo', $request, $choiceList, 'json');
    }

    protected function tearDown()
    {
        $this->event = null;
    }

    public function testAjaxChoiceListAction()
    {
        $validData = array(
            'length'      => null,
            'page_number' => null,
            'page_size'   => null,
            'search'      => null,
            'results'     => null,
        );

        $this->assertSame($validData, $this->event->getData());
    }
}
