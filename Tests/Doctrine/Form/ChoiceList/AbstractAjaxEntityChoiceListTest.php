<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Doctrine\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxEntityChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;
use Symfony\Bridge\Doctrine\Tests\Form\ChoiceList\AbstractEntityChoiceListTest;

/**
 * Abstract tests case for AJAX entity choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAjaxEntityChoiceListTest extends AbstractEntityChoiceListTest
{
    /**
     * @return AjaxChoiceListInterface
     */
    protected function createChoiceList()
    {
        return new AjaxEntityChoiceList(new FixtureAjaxChoiceListFormatter(), $this->em, $this->getEntityClass());
    }
}
