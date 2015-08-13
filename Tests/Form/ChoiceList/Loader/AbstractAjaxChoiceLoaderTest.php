<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Loader;

/**
 * Base tests case for ajax choice loader.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAjaxChoiceLoaderTest extends AbstractChoiceLoaderTest
{
    /**
     * @param bool $group
     *
     * @return array
     */
    abstract protected function getValidStructuredValuesForSearch($group);

    /**
     * @param bool $group
     * @param int  $pageNumber
     * @param int  $pageSize
     *
     * @return array
     */
    abstract protected function getValidStructuredValuesForPagination($group, $pageNumber, $pageSize);

    /**
     * @dataProvider getIsGroup
     *
     * @param bool $group
     */
    public function testDefaultAjax($group)
    {
        $loader = $this->createChoiceLoader($group);

        $this->assertEquals(10, $loader->getPageSize());
        $this->assertEquals(1, $loader->getPageNumber());
        $this->assertSame('', $loader->getSearch());
        $this->assertCount(0, $loader->getIds());

        $loader->setPageSize(1);
        $loader->setPageNumber(2);
        $loader->setSearch('Foo');
        $loader->setIds(array('2'));

        $this->assertEquals(1, $loader->getPageSize());
        $this->assertEquals(2, $loader->getPageNumber());
        $this->assertSame('Foo', $loader->getSearch());
        $this->assertCount(1, $loader->getIds());
    }

    /**
     * @dataProvider getIsGroup
     *
     * @param bool $group
     */
    public function testSearch($group)
    {
        $loader = $this->createChoiceLoader($group);
        $loader->setSearch('ba');
        $loader->reset();

        $this->assertEquals($this->getValidStructuredValuesForSearch($group), $loader->loadChoiceList()->getStructuredValues());
    }

    public function getPagination()
    {
        return array(
            array(false, 1, 2),
            array(true, 1, 2),
            array(false, 1, 0),
            array(true, 1, 0),
            array(false, 1, -1),
            array(true, 1, -1),
            array(false, 0, 2),
            array(true, 0, 2),
            array(false, -1, 2),
            array(true, -1, 2),
            array(false, 2, 2),
            array(true, 2, 2),
        );
    }

    /**
     * @dataProvider getPagination
     *
     * @param bool $group
     * @param int  $pageNumber
     * @param int  $pageSize
     */
    public function testLoadPaginatedChoiceList($group, $pageNumber, $pageSize)
    {
        $loader = $this->createChoiceLoader($group);
        $loader->setPageNumber($pageNumber);
        $loader->setPageSize($pageSize);
        $loader->reset();

        $this->assertEquals($this->getValidStructuredValuesForPagination($group, $pageNumber, $pageSize), $loader->loadPaginatedChoiceList()->getStructuredValues());
    }
}
