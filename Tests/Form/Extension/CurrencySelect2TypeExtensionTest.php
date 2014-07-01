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
 * Tests case for currency of select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CurrencySelect2TypeExtensionTest extends AbstractSelect2TypeExtensionTest
{
    protected function getExtensionTypeName()
    {
        return 'currency';
    }

    protected function getSingleData()
    {
        return 'EUR';
    }

    protected function getValidSingleValue()
    {
        return 'EUR';
    }

    protected function getValidAjaxSingleValue()
    {
        return 'EUR';
    }

    protected function getMultipleData()
    {
        return array('EUR', 'USD');
    }

    protected function getValidMultipleValue()
    {
        return array('EUR', 'USD');
    }

    protected function getValidAjaxMultipleValue()
    {
        return implode(',', $this->getValidMultipleValue());
    }

    protected function getValidFirstChoiceSelected()
    {
        $formatter = new Select2AjaxChoiceListFormatter();
        $choice = new ChoiceView('XUA', 'XUA', 'ADB Unit of Account');

        return $formatter->formatChoice($choice);
    }
}
