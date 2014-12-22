<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * Tests case for AJAX empty choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SearchNestedAjaxSimpleChoiceListTest extends AbstractAjaxChoiceListTest
{
    protected function createChoiceList()
    {
        $list = new AjaxSimpleChoiceList(new FixtureAjaxChoiceListFormatter(), array(
            'Group 1' => array(
                'a' => 'A',
                'b' => 'B',
            ),
            'Group 2' => array(
                'c' => 'C',
                'd' => 'D',
            ),
        ), array('b', 'c'));
        $list->setSearch('B');
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
            0 => 'b',
        );
    }

    protected function getValidValues()
    {
        return array(
            0 => 'b',
        );
    }

    protected function getChoicesForValuesData()
    {
        return array('c', 'b');
    }

    protected function getValidChoicesForValues()
    {
        return array(
            1 => 'b',
        );
    }

    protected function getValuesForChoicesData()
    {
        return array('c', 'b');
    }

    protected function getValidValuesForChoices()
    {
        return array(
            1 => 'b',
        );
    }

    protected function getValidPreferredViews()
    {
        return array(
            'Group 1' => array(
                0 => new ChoiceView('b', 'b', 'B'),
            ),
        );
    }

    protected function getValidRemainingViews()
    {
        return array();
    }

    protected function getValidFirstChoiceView()
    {
        return new ChoiceView('b', 'b', 'B');
    }

    protected function getFormattedChoicesForValuesData()
    {
        return array('c', 'b');
    }

    protected function getValidFormattedChoicesForValues()
    {
        return array(
            0 => array('value' => 'b', 'label' => 'B'),
        );
    }

    protected function getValidFormattedChoices()
    {
        return array(
            0 => array(
                'label' => 'Group 1',
                'choices' => array(
                    0 => array('value' => 'b', 'label' => 'B'),
                ),
            ),
        );
    }

    protected function getAllowAddChoicesForValuesData()
    {
        return array('c', 'b', 'z');
    }

    protected function getValidAllowAddChoicesForValues()
    {
        return array(
            1 => 'b',
            2 => 'c',
            3 => 'z',
        );
    }

    protected function getAllowAddValuesForChoicesData()
    {
        return array('c', 'b', 'z');
    }

    protected function getValidAllowAddValuesForChoices()
    {
        return array(
            1 => 'b',
            2 => 'c',
            3 => 'z',
        );
    }

    protected function getAllowAddFormattedChoicesForValuesData()
    {
        return array('c', 'b', 'z');
    }

    protected function getValidAllowAddFormattedChoicesForValues()
    {
        return array(
            0 => array('value' => 'b', 'label' => 'B'),
            1 => array('value' => 'c', 'label' => 'c'),
            2 => array('value' => 'z', 'label' => 'z'),
        );
    }

    protected function getValidPaginationFormattedChoices()
    {
        return array(
            0 => array(
                'label' => 'Group 1',
                'choices' => array(
                    0 => array('value' => 'b', 'label' => 'B'),
                ),
            ),
        );
    }
}
