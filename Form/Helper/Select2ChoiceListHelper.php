<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\Helper;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\Select2AjaxChoiceListFormatter;

/**
 * Helper for generate the AJAX response for the select2 form choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Select2ChoiceListHelper extends AjaxChoiceListHelper
{
    /**
     * {@inheritdoc}
     */
    protected static function createChoiceListFormatter()
    {
        return new Select2AjaxChoiceListFormatter();
    }
}
