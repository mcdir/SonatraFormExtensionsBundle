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
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

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
            'a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'
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

    protected function getFormattedChoices()
    {
        return array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'a', 'text' => 'A'), 2 => array('id' => 'c', 'text' => 'C'), 3 => array('id' => 'd', 'text' => 'D'));
    }

    protected function getPreferredViews()
    {
        return array(1 => new ChoiceView('b', 'b', 'B'));
    }

    protected function getRemainingViews()
    {
        return array(0 => new ChoiceView('a', 'a', 'A'), 2 => new ChoiceView('c', 'c', 'C'), 3 => new ChoiceView('d', 'd', 'D'));
    }

    protected function getChoicesForValues()
    {
        return array(0 => 'c', 1 => 'b');
    }

    protected function getListForLabelChoicesForValues()
    {
        return array('c', 'b');
    }

    protected function getLabelChoicesForValues()
    {
        return array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'c', 'text' => 'C'));
    }

    protected function getQueryForSearchChoices()
    {
        return 'A';
    }

    protected function getSearchChoices()
    {
        return array(0 => array('id' => 'a', 'text' => 'A'));
    }

    protected function getFormattedGroupChoices()
    {
        return array(0 => array('text' => 'Group 1', 'children' => array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'a', 'text' => 'A'))), 1 => array('text' => 'Group 2', 'children' => array(0 => array('id' => 'c', 'text' => 'C'), 1 => array('id' => 'd', 'text' => 'D'))));
    }

    protected function getGroupPreferredViews()
    {
        return array(
            'Group 1' => array(1 => new ChoiceView('b', 'b', 'B')),
            'Group 2' => array(2 => new ChoiceView('c', 'c', 'C'))
        );
    }

    protected function getGroupRemainingViews()
    {
        return array(
            'Group 1' => array(0 => new ChoiceView('a', 'a', 'A')),
            'Group 2' => array(3 => new ChoiceView('d', 'd', 'D'))
        );
    }

    protected function getQueryForSearchGroupChoices()
    {
        return 'A';
    }

    protected function getSearchGroupChoices()
    {
        return array(0 => array('text' => 'Group 1', 'children' => array(0 => array('id' => 'a', 'text' => 'A'))));
    }

    protected function getListForNonexistentChoicesForValues()
    {
        return array('z');
    }

    protected function getNonexistentChoicesForValues()
    {
        return array(0 => 'z');
    }

    protected function getNonexistentLabelChoicesForValues()
    {
        return array(0 => array('id' => 'b', 'text' => 'B'), 1 => array('id' => 'c', 'text' => 'C'), 2 => array('id' => 'z', 'text' => 'z'));
    }
}
