<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Formatter;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\Select2AjaxChoiceListFormatter;

/**
 * Tests case for select2 ajax choice list formatter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Select2AjaxChoiceListFormatterTest extends AbstractAjaxChoiceListFormatterTest
{
    protected function createFormatter()
    {
        return new Select2AjaxChoiceListFormatter();
    }

    protected function getValidFormattedChoice()
    {
        return array(
            'id'   => 'foo',
            'text' => 'Bar',
        );
    }

    protected function getValidFormattedGroupChoice()
    {
        return array(
            'text'     => 'Baz',
            'children' => array(),
        );
    }

    protected function getValidGroupWithChoiceAdded()
    {
        $group = $this->getValidFormattedGroupChoice();
        $group['children'][] = $this->getValidFormattedChoice();

        return $group;
    }
}
