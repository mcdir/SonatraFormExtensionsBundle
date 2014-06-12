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
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * Abstract tests case for AJAX choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAjaxChoiceListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AjaxChoiceListInterface
     */
    protected $list;

    protected function setUp()
    {
        $this->list = $this->createChoiceList();
    }

    protected function tearDown()
    {
        $this->list = null;
    }

    public function testGetSizes()
    {
        $this->assertEquals($this->getValidSize(), $this->list->getSize());
    }

    public function testGetChoices()
    {
        $this->assertSame($this->getValidChoices(), $this->list->getChoices());
        $this->list->reset();
        $this->assertSame($this->getValidChoices(), $this->list->getChoices());
    }

    public function testGetValues()
    {
        $this->assertSame($this->getValidValues(), $this->list->getValues());
        $this->list->reset();
        $this->assertSame($this->getValidValues(), $this->list->getValues());
    }

    public function testGetChoicesForValues()
    {
        $this->assertSame($this->getValidChoicesForValues(), $this->list->getChoicesForValues($this->getChoicesForValuesData()));
        $this->list->reset();
        $this->assertSame($this->getValidChoicesForValues(), $this->list->getChoicesForValues($this->getChoicesForValuesData()));
    }

    public function testGetValuesForChoices()
    {
        $this->assertSame($this->getValidValuesForChoices(), $this->list->getChoicesForValues($this->getValuesForChoicesData()));
        $this->list->reset();
        $this->assertSame($this->getValidValuesForChoices(), $this->list->getChoicesForValues($this->getValuesForChoicesData()));
    }

    public function testGetPreferredViews()
    {
        $this->assertEquals($this->getValidPreferredViews(), $this->list->getPreferredViews());
        $this->list->reset();
        $this->assertEquals($this->getValidPreferredViews(), $this->list->getPreferredViews());
    }

    public function testGetRemainingViews()
    {
        $this->assertEquals($this->getValidRemainingViews(), $this->list->getRemainingViews());
        $this->list->reset();
        $this->assertEquals($this->getValidRemainingViews(), $this->list->getRemainingViews());
    }

    public function testGetFirstChoiceView()
    {
        $this->assertEquals($this->getValidFirstChoiceView(), $this->list->getFirstChoiceView());
        $this->list->reset();
        $this->assertEquals($this->getValidFirstChoiceView(), $this->list->getFirstChoiceView());
    }

    public function testGetFormattedChoicesForValues()
    {
        $this->assertEquals($this->getValidFormattedChoicesForValues(), $this->list->getFormattedChoicesForValues($this->getFormattedChoicesForValuesData()));
        $this->list->reset();
        $this->assertEquals($this->getValidFormattedChoicesForValues(), $this->list->getFormattedChoicesForValues($this->getFormattedChoicesForValuesData()));
    }

    public function testGetFormattedChoices()
    {
        $this->assertEquals($this->getValidFormattedChoices(), $this->list->getFormattedChoices());
        $this->list->reset();
        $this->assertEquals($this->getValidFormattedChoices(), $this->list->getFormattedChoices());
    }

    public function testAllowAddChoicesForValues()
    {
        $this->list->setAllowAdd(true);
        $this->list->reset();

        $this->assertEquals($this->getValidAllowAddChoicesForValues(), $this->list->getChoicesForValues($this->getAllowAddChoicesForValuesData()));
    }

    public function testAllowAddValuesForChoices()
    {
        $this->list->setAllowAdd(true);
        $this->list->reset();

        $this->assertEquals($this->getValidAllowAddValuesForChoices(), $this->list->getValuesForChoices($this->getAllowAddValuesForChoicesData()));
    }

    public function testAllowAddFormattedChoicesForValues()
    {
        $this->list->setAllowAdd(true);
        $this->list->reset();

        $this->assertEquals($this->getValidAllowAddFormattedChoicesForValues(), $this->list->getFormattedChoicesForValues($this->getAllowAddFormattedChoicesForValuesData()));
    }

    public function testPagination()
    {
        $this->list->setPageSize(1);
        $this->list->reset();

        $this->assertEquals($this->getValidSize(), $this->list->getSize());
        $this->assertEquals($this->getValidPaginationFormattedChoices(), $this->list->getFormattedChoices());
    }

    public function testWithoutPagination()
    {
        $this->list->setPageSize(0);
        $this->list->reset();

        $this->assertEquals($this->getValidSize(), $this->list->getSize());
        $this->assertSame($this->getValidChoices(), $this->list->getChoices());
        $this->assertSame($this->getValidValues(), $this->list->getValues());
        $this->assertSame($this->getValidChoicesForValues(), $this->list->getChoicesForValues($this->getChoicesForValuesData()));
        $this->assertSame($this->getValidValuesForChoices(), $this->list->getChoicesForValues($this->getValuesForChoicesData()));
        $this->assertEquals($this->getValidPreferredViews(), $this->list->getPreferredViews());
        $this->assertEquals($this->getValidRemainingViews(), $this->list->getRemainingViews());
        $this->assertEquals($this->getValidFirstChoiceView(), $this->list->getFirstChoiceView());
        $this->assertEquals($this->getValidFormattedChoicesForValues(), $this->list->getFormattedChoicesForValues($this->getFormattedChoicesForValuesData()));
        $this->assertEquals($this->getValidFormattedChoices(), $this->list->getFormattedChoices());
    }

    /**
     * Creates choice list for tests.
     *
     * @return AjaxChoiceListInterface
     */
    abstract protected function createChoiceList();

    /**
     * Gets choice list size.
     *
     * @return int
     */
    abstract protected function getValidSize();

    /**
     * Valid data for getChoices test.
     *
     * @return array
     */
    abstract protected function getValidChoices();

    /**
     * Valid data for getValues test.
     *
     * @return array
     */
    abstract protected function getValidValues();

    /**
     * Data for getChoicesForValues test.
     *
     * @return array
     */
    abstract protected function getChoicesForValuesData();

    /**
     * Valid data for getChoicesForValues test.
     *
     * @return array
     */
    abstract protected function getValidChoicesForValues();

    /**
     * Data for getValuesForChoices test.
     *
     * @return array
     */
    abstract protected function getValuesForChoicesData();

    /**
     * Valid data for getValuesForChoices test.
     *
     * @return array
     */
    abstract protected function getValidValuesForChoices();

    /**
     * Valid data for getPreferredViews test.
     *
     * @return array
     */
    abstract protected function getValidPreferredViews();

    /**
     * Valid data for getRemainingViews test.
     *
     * @return array
     */
    abstract protected function getValidRemainingViews();

    /**
     * Valid data for getFirstChoiceView test.
     *
     * @return ChoiceView|null
     */
    abstract protected function getValidFirstChoiceView();

    /**
     * Data for getFormattedChoicesForValues test.
     *
     * @return array
     */
    abstract protected function getFormattedChoicesForValuesData();

    /**
     * Valid data for getFormattedChoicesForValues test.
     *
     * @return array
     */
    abstract protected function getValidFormattedChoicesForValues();

    /**
     * Valid data for getFormattedChoices test.
     *
     * @return array
     */
    abstract protected function getValidFormattedChoices();

    /**
     * Data for getChoicesForValues allow add test.
     *
     * @return array
     */
    abstract protected function getAllowAddChoicesForValuesData();

    /**
     * Valid data for getChoicesForValues allow add test.
     *
     * @return array
     */
    abstract protected function getValidAllowAddChoicesForValues();

    /**
     * Data for getValuesForChoices allow add test.
     *
     * @return array
     */
    abstract protected function getAllowAddValuesForChoicesData();

    /**
     * Valid data for getValuesForChoices allow add test.
     *
     * @return array
     */
    abstract protected function getValidAllowAddValuesForChoices();

    /**
     * Data for getFormattedChoicesForValues allow add test.
     *
     * @return array
     */
    abstract protected function getAllowAddFormattedChoicesForValuesData();

    /**
     * Valid data for getFormattedChoicesForValues allow add test.
     *
     * @return array
     */
    abstract protected function getValidAllowAddFormattedChoicesForValues();

    /**
     * Valid data for getFormattedChoices pagination test.
     *
     * @return array
     */
    abstract protected function getValidPaginationFormattedChoices();
}
