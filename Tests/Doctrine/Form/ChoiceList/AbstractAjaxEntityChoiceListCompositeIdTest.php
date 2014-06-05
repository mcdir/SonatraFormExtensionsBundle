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
use Symfony\Bridge\Doctrine\Tests\Fixtures\CompositeIntIdEntity;

/**
 * Abstract tests case for AJAX entity choice list with composite id.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAjaxEntityChoiceListCompositeIdTest extends AbstractAjaxEntityChoiceListTest
{
    protected function getEntityClass()
    {
        return 'Symfony\Bridge\Doctrine\Tests\Fixtures\CompositeIntIdEntity';
    }

    /**
     * @return AjaxChoiceListInterface
     */
    protected function createObjects()
    {
        return array(
            new CompositeIntIdEntity(10, 11, 'A'),
            new CompositeIntIdEntity(20, 21, 'B'),
            new CompositeIntIdEntity(30, 31, 'C'),
            new CompositeIntIdEntity(40, 41, 'D'),
        );
    }

    protected function getChoices()
    {
        return array(0 => $this->obj1, 1 => $this->obj2, 2 => $this->obj3, 3 => $this->obj4);
    }

    protected function getLabels()
    {
        return array(0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D');
    }

    protected function getValues()
    {
        return array(0 => '0', 1 => '1', 2 => '2', 3 => '3');
    }

    protected function getIndices()
    {
        return array(0, 1, 2, 3);
    }
}
