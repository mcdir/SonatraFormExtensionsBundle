<?php

/**
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
 * Tests case for timezone of select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class TimezoneSelect2TypeExtensionTest extends AbstractSelect2TypeExtensionTest
{
    protected function getExtensionTypeName()
    {
        return 'timezone';
    }

    protected function getSingleData()
    {
        return 'Europe/Paris';
    }

    protected function getValidSingleValue()
    {
        return 'Europe/Paris';
    }

    protected function getValidAjaxSingleValue()
    {
        return 'Europe/Paris';
    }

    protected function getMultipleData()
    {
        return array('Europe/Paris', 'America/Los_Angeles');
    }

    protected function getValidMultipleValue()
    {
        return array('Europe/Paris', 'America/Los_Angeles');
    }

    protected function getValidAjaxMultipleValue()
    {
        return implode(',', $this->getValidMultipleValue());
    }

    protected function getValidFirstChoiceSelected()
    {
        $formatter = new Select2AjaxChoiceListFormatter();
        $choice = new ChoiceView('Africa/Abidjan', 'Africa/Abidjan', 'Abidjan');

        return $formatter->formatChoice($choice);
    }
}
