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
use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxORMQueryBuilderLoader;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class UnloadedAjaxEntityChoiceListSingleIntIdWithQueryBuilderTest extends UnloadedAjaxEntityChoiceListSingleIntIdTest
{
    /**
     * @return AjaxChoiceListInterface
     */
    protected function createChoiceList()
    {
        $qb = $this->em->createQueryBuilder()->select('s')->from($this->getEntityClass(), 's');
        $loader = new AjaxORMQueryBuilderLoader($qb);

        return new AjaxEntityChoiceList(new FixtureAjaxChoiceListFormatter(), $this->em, $this->getEntityClass(), null, $loader);
    }
}
