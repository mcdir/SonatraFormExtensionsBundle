<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Doctrine\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class LoadedAjaxEntityChoiceListCompositeIdTest extends AbstractAjaxEntityChoiceListCompositeIdTest
{
    /**
     * @return AjaxChoiceListInterface
     */
    protected function createChoiceList()
    {
        $list = parent::createChoiceList();

        // load list
        $list->getChoices();

        return $list;
    }
}
