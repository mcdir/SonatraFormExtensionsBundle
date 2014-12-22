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

/**
 * Tests case for AJAX empty choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class EmptyAjaxSimpleChoiceListTest extends AbstractAjaxChoiceListTest
{
    protected function createChoiceList()
    {
        return new AjaxSimpleChoiceList(new FixtureAjaxChoiceListFormatter(), array(), array());
    }

    protected function getValidSize()
    {
        return 0;
    }

    protected function getValidChoices()
    {
        return array();
    }

    protected function getValidValues()
    {
        return array();
    }

    protected function getChoicesForValuesData()
    {
        return array();
    }

    protected function getValidChoicesForValues()
    {
        return array();
    }

    protected function getValuesForChoicesData()
    {
        return array();
    }

    protected function getValidValuesForChoices()
    {
        return array();
    }

    protected function getValidPreferredViews()
    {
        return array();
    }

    protected function getValidRemainingViews()
    {
        return array();
    }

    protected function getValidFirstChoiceView()
    {
        return;
    }

    protected function getFormattedChoicesForValuesData()
    {
        return array();
    }

    protected function getValidFormattedChoicesForValues()
    {
        return array();
    }

    protected function getValidFormattedChoices()
    {
        return array();
    }

    protected function getAllowAddChoicesForValuesData()
    {
        return array('c', 'b', 'z');
    }

    protected function getValidAllowAddChoicesForValues()
    {
        return array(
            0 => 'c',
            1 => 'b',
            2 => 'z',
        );
    }

    protected function getAllowAddValuesForChoicesData()
    {
        return array('c', 'b', 'z');
    }

    protected function getValidAllowAddValuesForChoices()
    {
        return array(
            0 => 'c',
            1 => 'b',
            2 => 'z',
        );
    }

    protected function getAllowAddFormattedChoicesForValuesData()
    {
        return array('c', 'b', 'z');
    }

    protected function getValidAllowAddFormattedChoicesForValues()
    {
        return array(
            0 => array('value' => 'c', 'label' => 'c'),
            1 => array('value' => 'b', 'label' => 'b'),
            2 => array('value' => 'z', 'label' => 'z'),
        );
    }

    protected function getValidPaginationFormattedChoices()
    {
        return array();
    }
}
