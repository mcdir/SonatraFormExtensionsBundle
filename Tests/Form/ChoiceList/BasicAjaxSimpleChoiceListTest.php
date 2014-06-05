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

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * Tests case for basic AJAX simple choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BasicAjaxSimpleChoiceListTest extends AbstractAjaxChoiceListTest
{
    protected function createChoiceList()
    {
        return new AjaxSimpleChoiceList(new FixtureAjaxChoiceListFormatter(), array(
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            'd' => 'D',
        ), array('b'));
    }

    protected function getValidSize()
    {
        return 4;
    }

    protected function getValidChoices()
    {
        return array(
            0 => 'a',
            1 => 'b',
            2 => 'c',
            3 => 'd',
        );
    }

    protected function getValidValues()
    {
        return array(
            0 => 'a',
            1 => 'b',
            2 => 'c',
            3 => 'd',
        );
    }

    protected function getChoicesForValuesData()
    {
        return array('c', 'b');
    }

    protected function getValidChoicesForValues()
    {
        return array(
            0 => 'c',
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
            0 => 'c',
            1 => 'b',
        );
    }

    protected function getValidPreferredViews()
    {
        return array(
            1 => new ChoiceView('b', 'b', 'B'),
        );
    }

    protected function getValidRemainingViews()
    {
        return array(
            0 => new ChoiceView('a', 'a', 'A'),
            2 => new ChoiceView('c', 'c', 'C'),
            3 => new ChoiceView('d', 'd', 'D'),
        );
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
            1 => array('value' => 'c', 'label' => 'C'),
        );
    }

    protected function getValidFormattedChoices()
    {
        return array(
            0 => array('value' => 'b', 'label' => 'B'),
            1 => array('value' => 'a', 'label' => 'A'),
            2 => array('value' => 'c', 'label' => 'C'),
            3 => array('value' => 'd', 'label' => 'D'),
        );
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
            0 => array('value' => 'b', 'label' => 'B'),
            1 => array('value' => 'c', 'label' => 'C'),
            2 => array('value' => 'z', 'label' => 'z'),
        );
    }

    protected function getValidPaginationFormattedChoices()
    {
        return array(
            0 => array('value' => 'b', 'label' => 'B'),
        );
    }
}
