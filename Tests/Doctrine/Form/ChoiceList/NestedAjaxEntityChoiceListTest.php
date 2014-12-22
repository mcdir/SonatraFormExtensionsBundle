<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Doctrine\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxEntityChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxORMQueryBuilderLoader;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;
use Symfony\Bridge\Doctrine\Tests\Fixtures\GroupableEntity;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * Tests case for nested AJAX entity choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class NestedAjaxEntityChoiceListTest extends AbstractExtendAjaxEntityChoiceListTest
{
    protected function createChoiceList()
    {
        $item1 = new GroupableEntity(1, 'Foo', 'Group1');
        $item2 = new GroupableEntity(2, 'Bar', 'Group1');
        $item3 = new GroupableEntity(3, 'Baz', 'Group2');
        $item4 = new GroupableEntity(4, 'Boo!', 'Group2');

        $this->items = array(1 => $item1, 2 => $item2, 3 => $item3, 4 => $item4);

        $this->em->persist($item1);
        $this->em->persist($item2);
        $this->em->persist($item3);
        $this->em->persist($item4);
        $this->em->flush();

        $query = $this->em->createQueryBuilder()
            ->add('select', 'e')
            ->add('from', self::GROUPABLE_CLASS.' e')
        ;
        $loader = new AjaxORMQueryBuilderLoader($query);

        return new AjaxEntityChoiceList(
            new FixtureAjaxChoiceListFormatter(),
            $this->em,
            self::GROUPABLE_CLASS,
            'name',
            $loader,
            null,
            array(),
            'groupName'
        );
    }

    protected function getValidSize()
    {
        return 4;
    }

    protected function getValidChoices()
    {
        return array(
            1 => $this->items[1],
            2 => $this->items[2],
            3 => $this->items[3],
            4 => $this->items[4],
        );
    }

    protected function getValidValues()
    {
        return array(
            1 => '1',
            2 => '2',
            3 => '3',
            4 => '4',
        );
    }

    protected function getChoicesForValuesData()
    {
        return array('2', '4');
    }

    protected function getValidChoicesForValues()
    {
        return array(
            0 => $this->items[2],
            1 => $this->items[4],
        );
    }

    protected function getValuesForChoicesData()
    {
        return array('2', '4');
    }

    protected function getValidValuesForChoices()
    {
        return array(
            0 => $this->items[2],
            1 => $this->items[4],
        );
    }

    protected function getValidPreferredViews()
    {
        return array();
    }

    protected function getValidRemainingViews()
    {
        return array(
            'Group1' => array(
                1 => new ChoiceView($this->items[1], '1', 'Foo'),
                2 => new ChoiceView($this->items[2], '2', 'Bar'),
            ),
            'Group2' => array(
                3 => new ChoiceView($this->items[3], '3', 'Baz'),
                4 => new ChoiceView($this->items[4], '4', 'Boo!'),
            ),
        );
    }

    protected function getValidFirstChoiceView()
    {
        return new ChoiceView($this->items[1], '1', 'Foo');
    }

    protected function getFormattedChoicesForValuesData()
    {
        return array('2');
    }

    protected function getValidFormattedChoicesForValues()
    {
        return array(
            0 => array('value' => '2', 'label' => 'Bar'),
        );
    }

    protected function getValidFormattedChoices()
    {
        return array(
            0 => array(
                'label'   => 'Group1',
                'choices' => array(
                    0 => array('value' => '1', 'label' => 'Foo'),
                    1 => array('value' => '2', 'label' => 'Bar'),
                ),
            ),
            1 => array(
                'label'   => 'Group2',
                'choices' => array(
                    0 => array('value' => '3', 'label' => 'Baz'),
                    1 => array('value' => '4', 'label' => 'Boo!'),
                ),
            ),
        );
    }

    protected function getAllowAddChoicesForValuesData()
    {
        return array('1', '2', 'z');
    }

    protected function getValidAllowAddChoicesForValues()
    {
        return array(
            0 => $this->items[1],
            1 => $this->items[2],
            2 => 'z',
        );
    }

    protected function getAllowAddValuesForChoicesData()
    {
        return array($this->items[1], $this->items[2], 'z');
    }

    protected function getValidAllowAddValuesForChoices()
    {
        return array(
            0 => '1',
            1 => '2',
            2 => 'z',
        );
    }

    protected function getAllowAddFormattedChoicesForValuesData()
    {
        return array('1', '2', 'z');
    }

    protected function getValidAllowAddFormattedChoicesForValues()
    {
        return array(
            0 => array('value' => '1', 'label' => 'Foo'),
            1 => array('value' => '2', 'label' => 'Bar'),
            2 => array('value' => 'z', 'label' => 'z'),
        );
    }

    protected function getValidPaginationFormattedChoices()
    {
        return array(
            0 => array(
                'label' => 'Group1',
                'choices' => array(
                    0 => array('value' => '1', 'label' => 'Foo'),
                ),
            ),
        );
    }

    public function testGetSizeWithoutLoader()
    {
        $this->list = new AjaxEntityChoiceList(
            new FixtureAjaxChoiceListFormatter(),
            $this->em,
            self::GROUPABLE_CLASS,
            'name',
            null,
            null,
            array(),
            'groupName'
        );

        $this->assertEquals(4, $this->list->getSize());
    }
}
