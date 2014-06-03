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
        $this->assertEquals(4, $this->list->getSize());
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
        $this->list = $this->createSimpleChoiceList();
        $this->assertSame($this->getChoices(), $this->list->getValues());

        $this->list->setExtractValues(false);
        $this->assertSame($this->getChoices(), $this->list->getValues());

        $this->list->setExtractValues(true);
        $this->assertSame($this->getChoices(), $this->list->getValues());
    }

    public function testInitArray()
    {
        $this->list = $this->createSimpleChoiceList();

        $this->assertSame($this->getChoices(), $this->list->getChoices());
        $this->assertSame($this->getChoices(), $this->list->getValues());
        $this->assertEquals($this->getPreferredViews(), $this->list->getPreferredViews());
        $this->assertEquals($this->getRemainingViews(), $this->list->getRemainingViews());
        $this->assertSame($this->getFormattedChoices(), $this->list->getDataChoices());

        $this->assertSame($this->getChoicesForValues(), $this->list->getChoicesForValues(array('c', 'b')));
        $this->list->setAllowAdd(true);
        $this->assertSame($this->getChoicesForValues(), $this->list->getChoicesForValues(array('c', 'b')));
    }

    public function testInitArrayAjax()
    {
        $this->list = $this->createSimpleChoiceList();
        $this->list->setAjax(true);

        $this->assertSame($this->getFormattedChoices(), $this->list->getChoices());
        $this->assertSame($this->getValues(), $this->list->getValues());
        $this->assertEquals($this->getPreferredViews(), $this->list->getPreferredViews());
        $this->assertEquals($this->getRemainingViews(), $this->list->getRemainingViews());
        $this->assertSame($this->getLabelChoicesForValues(), $this->list->getLabelChoicesForValues($this->getListForLabelChoicesForValues()));
        $this->assertSame(array(), $this->list->getDataChoices());
        // cache
        $this->assertSame($this->getFormattedChoices(), $this->list->getChoices());
    }

    public function testInitArrayAjaxSearch()
    {
        $this->list = $this->createSimpleChoiceList();
        $this->list->setAjax(true);
        $this->list->setSearch($this->getQueryForSearchChoices());
        $this->assertSame($this->getSearchChoices(), $this->list->getChoices());
        $this->assertSame($this->getValues(), $this->list->getValues());
        $this->assertEquals($this->getPreferredViews(), $this->list->getPreferredViews());
        $this->assertEquals($this->getRemainingViews(), $this->list->getRemainingViews());
        $this->assertSame($this->getLabelChoicesForValues(), $this->list->getLabelChoicesForValues($this->getListForLabelChoicesForValues()));
        // cache
        $this->assertSame($this->getSearchChoices(), $this->list->getChoices());
    }

    public function testInitNestedArray()
    {
        $this->assertSame($this->getChoices(), $this->list->getChoices());
        $this->assertSame($this->getValues(), $this->list->getValues());
        $this->assertEquals($this->getGroupPreferredViews(), $this->list->getPreferredViews());
        $this->assertEquals($this->getGroupRemainingViews(), $this->list->getRemainingViews());
        $this->assertSame($this->getLabelChoicesForValues(), $this->list->getLabelChoicesForValues($this->getListForLabelChoicesForValues()));
        $this->assertSame($this->getFormattedGroupChoices(), $this->list->getDataChoices());
    }

    public function testInitNestedArrayAjax()
    {
        $this->list->setAjax(true);

        $this->assertSame($this->getFormattedGroupChoices(), $this->list->getChoices());
        $this->assertSame($this->getValues(), $this->list->getValues());
        $this->assertEquals($this->getGroupPreferredViews(), $this->list->getPreferredViews());
        $this->assertEquals($this->getGroupRemainingViews(), $this->list->getRemainingViews());
        $this->assertSame($this->getLabelChoicesForValues(), $this->list->getLabelChoicesForValues($this->getListForLabelChoicesForValues()));
        $this->assertSame(array(), $this->list->getDataChoices());
        // cache
        $this->assertSame($this->getFormattedGroupChoices(), $this->list->getChoices());
    }

    public function testInitNestedArrayAjaxSearch()
    {
        $this->list->setAjax(true);
        $this->list->setSearch($this->getQueryForSearchGroupChoices());

        $this->assertSame($this->getSearchGroupChoices(), $this->list->getChoices());
        $this->assertSame($this->getValues(), $this->list->getValues());
        $this->assertEquals($this->getGroupPreferredViews(), $this->list->getPreferredViews());
        $this->assertEquals($this->getGroupRemainingViews(), $this->list->getRemainingViews());
        // cache
        $this->assertSame($this->getSearchGroupChoices(), $this->list->getChoices());
    }

    public function testNonexistentValues()
    {
        $this->assertSame($this->getNonexistentLabelChoicesForValues(), $this->list->getLabelChoicesForValues(array('c', 'b', 'z')));

        $this->list->setAllowAdd(true);
        $this->assertSame($this->getNonexistentChoicesForValues(), $this->list->getChoicesForValues($this->getListForNonexistentChoicesForValues()));
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

    /**
     * @return array
     */
    abstract protected function getFormattedChoices();

    /**
     * @return array
     */
    abstract protected function getPreferredViews();

    /**
     * @return array
     */
    abstract protected function getRemainingViews();

    /**
     * @return array
     */
    abstract protected function getChoicesForValues();

    /**
     * @return array
     */
    abstract protected function getListForLabelChoicesForValues();

    /**
     * @return array
     */
    abstract protected function getLabelChoicesForValues();

    /**
     * @return array
     */
    abstract protected function getQueryForSearchChoices();

    /**
     * @return array
     */
    abstract protected function getSearchChoices();

    /**
     * @return array
     */
    abstract protected function getFormattedGroupChoices();

    /**
     * @return array
     */
    abstract protected function getGroupPreferredViews();

    /**
     * @return array
     */
    abstract protected function getGroupRemainingViews();

    /**
     * @return array
     */
    abstract protected function getQueryForSearchGroupChoices();

    /**
     * @return array
     */
    abstract protected function getSearchGroupChoices();

    /**
     * @return array
     */
    abstract protected function getListForNonexistentChoicesForValues();

    /**
     * @return array
     */
    abstract protected function getNonexistentChoicesForValues();

    /**
     * @return array
     */
    abstract protected function getNonexistentLabelChoicesForValues();
}
