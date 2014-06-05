<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;
use Symfony\Component\Form\Tests\Extension\Core\ChoiceList\AbstractChoiceListTest;

/**
 * Tests case for AJAX simple choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxSimpleChoiceListTest extends AbstractChoiceListTest
{
    /**
     * @var AjaxChoiceListInterface
     */
    protected $list;

    protected function createChoiceList()
    {
        return new AjaxSimpleChoiceList(new FixtureAjaxChoiceListFormatter(), array(
            'Group 1' => array('a' => 'A', 'b' => 'B'),
            'Group 2' => array('c' => 'C', 'd' => 'D'),
        ), array('b', 'c'));
    }

    protected function getChoices()
    {
        return array(0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd');
    }

    protected function getLabels()
    {
        return array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D');
    }

    protected function getValues()
    {
        return array(0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd');
    }

    protected function getIndices()
    {
        return array(0, 1, 2, 3);
    }

    public function testDefaultConfig()
    {
        $this->assertFalse($this->list->getAllowAdd());
        $this->assertEquals(10, $this->list->getPageSize());
        $this->assertEquals(1, $this->list->getPageNumber());
        $this->assertEquals('', $this->list->getSearch());
        $this->assertCount(0, $this->list->getIds());
    }

    public function testCustomConfig()
    {
        $this->list->setAllowAdd(true);
        $this->list->setPageSize(20);
        $this->list->setPageNumber(2);
        $this->list->setSearch('search');
        $this->list->setIds(array('id1', 'id2'));

        $this->assertTrue($this->list->getAllowAdd());
        $this->assertEquals(20, $this->list->getPageSize());
        $this->assertEquals(2, $this->list->getPageNumber());
        $this->assertEquals('search', $this->list->getSearch());
        $this->assertCount(2, $this->list->getIds());
    }

    /**
     * @dataProvider dirtyValuesProvider
     */
    public function testGetValuesForChoicesDealsWithDirtyValues($choice, $value)
    {
        $choices = array(
            '0' => 'Zero',
            '1' => 'One',
            '' => 'Empty',
            '1.23' => 'Float',
            'foo' => 'Foo',
            'foo10' => 'Foo 10',
        );

        $this->list = new AjaxSimpleChoiceList(new FixtureAjaxChoiceListFormatter(), $choices, array());

        $this->assertSame(array($value), $this->list->getValuesForChoices(array($choice)));
    }

    public function dirtyValuesProvider()
    {
        return array(
            array(0, '0'),
            array('0', '0'),
            array('1', '1'),
            array(false, '0'),
            array(true, '1'),
            array('', ''),
            array(null, ''),
            array('1.23', '1.23'),
            array('foo', 'foo'),
            array('foo10', 'foo10'),
        );
    }
}
