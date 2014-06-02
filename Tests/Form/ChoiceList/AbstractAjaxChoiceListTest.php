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
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\Tests\Extension\Core\ChoiceList\AbstractChoiceListTest;

/**
 * Abstract tests case for AJAX choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAjaxChoiceListTest extends AbstractChoiceListTest
{
    /**
     * @var AjaxChoiceListInterface
     */
    protected $list;

    public function testDefaultConfig()
    {
        $this->list = $this->createSimpleChoiceList();

        $this->assertFalse($this->list->getAllowAdd());
        $this->assertFalse($this->list->getAjax());
        $this->assertEquals(10, $this->list->getPageSize());
        $this->assertEquals(1, $this->list->getPageNumber());
        $this->assertEquals('', $this->list->getSearch());
        $this->assertCount(0, $this->list->getIds());
        $this->assertEquals(3, $this->list->getSize());
    }

    public function testCustomConfig()
    {
        $this->list = $this->createSimpleChoiceList();

        $this->list->setAllowAdd(true);
        $this->list->setAjax(true);
        $this->list->setPageSize(20);
        $this->list->setPageNumber(2);
        $this->list->setSearch('search');
        $this->list->setIds(array('id1', 'id2'));

        $this->assertTrue($this->list->getAllowAdd());
        $this->assertTrue($this->list->getAjax());
        $this->assertEquals(20, $this->list->getPageSize());
        $this->assertEquals(2, $this->list->getPageNumber());
        $this->assertEquals('search', $this->list->getSearch());
        $this->assertCount(2, $this->list->getIds());
        $this->assertEquals(0, $this->list->getSize());
    }

    public function testGetSizeWithGroupList()
    {
        $this->assertFalse($this->list->getAllowAdd());
        $this->assertFalse($this->list->getAjax());
        $this->assertEquals(4, $this->list->getSize());
    }

    public function testExtractValues()
    {
        $sameValues = array(0 => 'a', 1 => 'b', 2 => 'c');

        $this->list = $this->createSimpleChoiceList();
        $this->assertSame($sameValues, $this->list->getValues());

        $this->list->setExtractValues(false);
        $this->assertSame($sameValues, $this->list->getValues());

        $this->list->setExtractValues(true);
        $this->assertSame($sameValues, $this->list->getValues());
    }

    public function testInitArray()
    {
        $validChoices = array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'a', 'text' => 'A'), 2 => array('id' => 'c', 'text' => 'C'));

        $this->list = $this->createSimpleChoiceList();

        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c'), $this->list->getChoices());
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('b', 'b', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('a', 'a', 'A'), 2 => new ChoiceView('c', 'c', 'C')), $this->list->getRemainingViews());
        $this->assertSame($validChoices, $this->list->getDataChoices());

        $this->assertSame(array(0 => 'c', 1 => 'b'), $this->list->getChoicesForValues(array('c', 'b')));
        $this->list->setAllowAdd(true);
        $this->assertSame(array(0 => 'c', 1 => 'b'), $this->list->getChoicesForValues(array('c', 'b')));
    }

    public function testInitArrayAjax()
    {
        $validChoices = array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'a', 'text' => 'A'), 2 => array('id' => 'c', 'text' => 'C'));

        $this->list = $this->createSimpleChoiceList();
        $this->list->setAjax(true);

        $this->assertSame($validChoices, $this->list->getChoices());
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('b', 'b', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('a', 'a', 'A'), 2 => new ChoiceView('c', 'c', 'C')), $this->list->getRemainingViews());
        $this->assertSame(array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'c', 'text' => 'C')), $this->list->getLabelChoicesForValues(array('c', 'b')));
        $this->assertSame(array(), $this->list->getDataChoices());
        // cache
        $this->assertSame($validChoices, $this->list->getChoices());
    }

    public function testInitArrayAjaxSearch()
    {
        $validChoices = array(0 => array('id' => 'a', 'text' => 'A'));

        $this->list = $this->createSimpleChoiceList();
        $this->list->setAjax(true);
        $this->list->setSearch('A');
        $this->assertSame($validChoices, $this->list->getChoices());
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c'), $this->list->getValues());
        $this->assertEquals(array(1 => new ChoiceView('b', 'b', 'B')), $this->list->getPreferredViews());
        $this->assertEquals(array(0 => new ChoiceView('a', 'a', 'A'), 2 => new ChoiceView('c', 'c', 'C')), $this->list->getRemainingViews());
        $this->assertSame(array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'c', 'text' => 'C')), $this->list->getLabelChoicesForValues(array('c', 'b')));
        // cache
        $this->assertSame($validChoices, $this->list->getChoices());
    }

    public function testInitNestedArray()
    {
        $validChoices = array(0 => array('text' => 'Group 1', 'children' => array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'a', 'text' => 'A'))), 1 => array('text' => 'Group 2', 'children' => array(0 => array('id' => 'c', 'text' => 'C'), 1 => array('id' => 'd', 'text' => 'D'))));

        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd'), $this->list->getChoices());
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd'), $this->list->getValues());
        $this->assertEquals(array(
                'Group 1' => array(1 => new ChoiceView('b', 'b', 'B')),
                'Group 2' => array(2 => new ChoiceView('c', 'c', 'C'))
            ), $this->list->getPreferredViews());
        $this->assertEquals(array(
                'Group 1' => array(0 => new ChoiceView('a', 'a', 'A')),
                'Group 2' => array(3 => new ChoiceView('d', 'd', 'D'))
            ), $this->list->getRemainingViews());
        $this->assertSame(array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'c', 'text' => 'C')), $this->list->getLabelChoicesForValues(array('c', 'b')));
        $this->assertSame($validChoices, $this->list->getDataChoices());
    }

    public function testInitNestedArrayAjax()
    {
        $validChoices = array(0 => array('text' => 'Group 1', 'children' => array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'a', 'text' => 'A'))), 1 => array('text' => 'Group 2', 'children' => array(0 => array('id' => 'c', 'text' => 'C'), 1 => array('id' => 'd', 'text' => 'D'))));

        $this->list->setAjax(true);

        $this->assertSame($validChoices, $this->list->getChoices());
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd'), $this->list->getValues());
        $this->assertEquals(array(
                'Group 1' => array(1 => new ChoiceView('b', 'b', 'B')),
                'Group 2' => array(2 => new ChoiceView('c', 'c', 'C'))
            ), $this->list->getPreferredViews());
        $this->assertEquals(array(
                'Group 1' => array(0 => new ChoiceView('a', 'a', 'A')),
                'Group 2' => array(3 => new ChoiceView('d', 'd', 'D'))
            ), $this->list->getRemainingViews());
        $this->assertSame(array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'c', 'text' => 'C')), $this->list->getLabelChoicesForValues(array('c', 'b')));
        $this->assertSame(array(), $this->list->getDataChoices());
        // cache
        $this->assertSame($validChoices, $this->list->getChoices());
    }

    public function testInitNestedArrayAjaxSearch()
    {
        $validChoices = array(0 => array('text' => 'Group 1', 'children' => array(0 => array('id' => 'a', 'text' => 'A'))));

        $this->list->setAjax(true);
        $this->list->setSearch('A');

        $this->assertSame($validChoices, $this->list->getChoices());
        $this->assertSame(array(0 => 'a', 1 => 'b', 2 => 'c', 3 => 'd'), $this->list->getValues());
        $this->assertEquals(array(
                'Group 1' => array(1 => new ChoiceView('b', 'b', 'B')),
                'Group 2' => array(2 => new ChoiceView('c', 'c', 'C'))
            ), $this->list->getPreferredViews());
        $this->assertEquals(array(
                'Group 1' => array(0 => new ChoiceView('a', 'a', 'A')),
                'Group 2' => array(3 => new ChoiceView('d', 'd', 'D'))
            ), $this->list->getRemainingViews());
        // cache
        $this->assertSame($validChoices, $this->list->getChoices());
    }

    public function testNonexistentValues()
    {
        $this->assertSame(array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'c', 'text' => 'C'), 2 => array('id' => 'z', 'text' => 'z')), $this->list->getLabelChoicesForValues(array('c', 'b', 'z')));

        $this->list->setAllowAdd(true);
        $this->assertSame(array(0 => 'z'), $this->list->getChoicesForValues(array('z')));
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

        $this->list = new AjaxSimpleChoiceList($choices, array());

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

    /**
     * @return AjaxChoiceListInterface
     */
    abstract protected function createSimpleChoiceList();
}
