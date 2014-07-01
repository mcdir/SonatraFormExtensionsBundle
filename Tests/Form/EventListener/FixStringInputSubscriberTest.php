<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\EventListener;

use Sonatra\Bundle\FormExtensionsBundle\Form\EventListener\FixStringInputSubscriber;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Tests case for fix string input listener.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FixStringInputSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testSubscribedEvents()
    {
        $filter = new FixStringInputSubscriber();

        $this->assertSame(array(
            FormEvents::PRE_SUBMIT => array('preSubmit', 10),
        ), $filter->getSubscribedEvents());
    }

    public function testFixStringWithMultiValues()
    {
        $data = '1,2,3';
        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $event = new FormEvent($form, $data);

        $filter = new FixStringInputSubscriber();
        $filter->preSubmit($event);

        $this->assertEquals(array('1', '2', '3'), $event->getData());
    }

    public function testFixStringWithSingleValue()
    {
        $data = '1';
        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $event = new FormEvent($form, $data);

        $filter = new FixStringInputSubscriber();
        $filter->preSubmit($event);

        $this->assertEquals(array('1'), $event->getData());
    }

    public function testFixStringArrayWithSingleValue()
    {
        $data = array('1');
        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $event = new FormEvent($form, $data);

        $filter = new FixStringInputSubscriber();
        $filter->preSubmit($event);

        $this->assertEquals(array('1'), $event->getData());
    }

    public function testFixStringWithEmptyValue()
    {
        $data = '';
        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $event = new FormEvent($form, $data);

        $filter = new FixStringInputSubscriber();
        $filter->preSubmit($event);

        $this->assertEquals(array(), $event->getData());
    }

    public function testFixStringArrayWithEmptyValue()
    {
        $data = array('');
        $form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $event = new FormEvent($form, $data);

        $filter = new FixStringInputSubscriber();
        $filter->preSubmit($event);

        $this->assertEquals(array(), $event->getData());
    }
}
