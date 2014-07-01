<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\Extension;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\Select2AjaxChoiceListFormatter;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * Tests case for choice of select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoiceSelect2TypeExtensionTest extends AbstractSelect2TypeExtensionTest
{
    protected function getChoices()
    {
        return array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D');
    }

    protected function getExtensionTypeName()
    {
        return 'choice';
    }

    protected function getSingleData()
    {
        return 1;
    }

    protected function getValidSingleValue()
    {
        return '1';
    }

    protected function getValidAjaxSingleValue()
    {
        return '1';
    }

    protected function getMultipleData()
    {
        return array('1', '2');
    }

    protected function getValidMultipleValue()
    {
        return array('1', '2');
    }

    protected function getValidAjaxMultipleValue()
    {
        return implode(',', $this->getValidMultipleValue());
    }

    protected function getValidFirstChoiceSelected()
    {
        $formatter = new Select2AjaxChoiceListFormatter();
        $choice = new ChoiceView('0', '0', 'A');

        return $formatter->formatChoice($choice);
    }
}
