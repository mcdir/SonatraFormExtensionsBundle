<?php

/**
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
use Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdEntity;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * Tests case for search AJAX entity choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SearchAjaxEntityChoiceListTest extends AbstractExtendAjaxEntityChoiceListTest
{
    protected function createChoiceList()
    {
        $item1 = new SingleIntIdEntity(1, 'Foo');
        $item2 = new SingleIntIdEntity(2, 'Bar');

        $this->items = array(1 => $item1, 2 => $item2);

        $this->em->persist($item1);
        $this->em->persist($item2);
        $this->em->flush();

        $query = $this->em->createQueryBuilder()
            ->add('select', 'e')
            ->add('from', self::SINGLE_INT_ID_CLASS . ' e')
        ;
        $loader = new AjaxORMQueryBuilderLoader($query);

        $list = new AjaxEntityChoiceList(
            new FixtureAjaxChoiceListFormatter(),
            $this->em,
            self::SINGLE_INT_ID_CLASS,
            'name',
            $loader
        );

        $list->setSearch('Bar');
        $list->reset();

        return $list;
    }

    protected function getValidSize()
    {
        return 1;
    }

    protected function getValidChoices()
    {
        return array(
            2 => $this->items[2],
        );
    }

    protected function getValidValues()
    {
        return array(
            2 => '2',
        );
    }

    protected function getChoicesForValuesData()
    {
        return array('2');
    }

    protected function getValidChoicesForValues()
    {
        return array(
            0 => $this->items[2],
        );
    }

    protected function getValuesForChoicesData()
    {
        return array('2');
    }

    protected function getValidValuesForChoices()
    {
        return array(
            0 => $this->items[2],
        );
    }

    protected function getValidPreferredViews()
    {
        return array();
    }

    protected function getValidRemainingViews()
    {
        return array(
            2 => new ChoiceView($this->items[2], '2', 'Bar'),
        );
    }

    protected function getValidFirstChoiceView()
    {
        return new ChoiceView($this->items[2], '2', 'Bar');
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
            0 => array('value' => '2', 'label' => 'Bar'),
        );
    }

    protected function getAllowAddChoicesForValuesData()
    {
        return array('1', '2', 'z');
    }

    protected function getValidAllowAddChoicesForValues()
    {
        return array(
            1 => $this->items[2],
            2 => '1',
            3 => 'z',
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
            0 => array('value' => '2', 'label' => 'Bar'),
            1 => array('value' => '1', 'label' => '1'),
            2 => array('value' => 'z', 'label' => 'z'),
        );
    }

    protected function getValidPaginationFormattedChoices()
    {
        return array(
            0 => array('value' => '2', 'label' => 'Bar'),
        );
    }
}
