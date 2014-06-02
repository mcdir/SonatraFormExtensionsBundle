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

/**
 * Tests case for AJAX simple choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxSimpleChoiceListTest extends AbstractAjaxChoiceListTest
{
    protected function createChoiceList()
    {
        return new AjaxSimpleChoiceList(array(
            'Group 1' => array('a' => 'A', 'b' => 'B'),
            'Group 2' => array('c' => 'C', 'd' => 'D'),
        ), array('b', 'c'));
    }

    protected function createSimpleChoiceList()
    {
        return new AjaxSimpleChoiceList(array(
            'a' => 'A', 'b' => 'B', 'c' => 'C'
        ), array('b'));
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
}
