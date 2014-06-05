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

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class UnloadedAjaxEntityChoiceListCompositeIdTest extends AbstractAjaxEntityChoiceListCompositeIdTest
{
    public function testGetIndicesForValuesIgnoresNonExistingValues()
    {
        // Non-existing values are not detected for unloaded choice lists
    }
}
