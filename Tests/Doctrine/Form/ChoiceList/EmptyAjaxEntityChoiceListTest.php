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

use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxEntityChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxORMQueryBuilderLoader;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;

/**
 * Tests case for empty AJAX entity choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class EmptyAjaxEntityChoiceListTest extends AbstractExtendAjaxEntityChoiceListTest
{
    protected function createChoiceList()
    {
        $query = $this->em->createQueryBuilder()
            ->add('select', 'e')
            ->add('from', self::SINGLE_INT_ID_CLASS . ' e')
        ;
        $loader = new AjaxORMQueryBuilderLoader($query);

        return new AjaxEntityChoiceList(
            new FixtureAjaxChoiceListFormatter(),
            $this->em,
            self::SINGLE_INT_ID_CLASS,
            null,
            $loader
        );
    }

    protected function getValidSize()
    {
        return 0;
    }

    protected function getValidChoices()
    {
        return array();
    }

    protected function getValidValues()
    {
        return array();
    }

    protected function getChoicesForValuesData()
    {
        return array();
    }

    protected function getValidChoicesForValues()
    {
        return array();
    }

    protected function getValuesForChoicesData()
    {
        return array();
    }

    protected function getValidValuesForChoices()
    {
        return array();
    }

    protected function getValidPreferredViews()
    {
        return array();
    }

    protected function getValidRemainingViews()
    {
        return array();
    }

    protected function getValidFirstChoiceView()
    {
        return null;
    }

    protected function getFormattedChoicesForValuesData()
    {
        return array();
    }

    protected function getValidFormattedChoicesForValues()
    {
        return array();
    }

    protected function getValidFormattedChoices()
    {
        return array();
    }

    protected function getAllowAddChoicesForValuesData()
    {
        return array('z');
    }

    protected function getValidAllowAddChoicesForValues()
    {
        return array(
            0 => 'z',
        );
    }

    protected function getAllowAddValuesForChoicesData()
    {
        return array('z');
    }

    protected function getValidAllowAddValuesForChoices()
    {
        return array(
            0 => 'z',
        );
    }

    protected function getAllowAddFormattedChoicesForValuesData()
    {
        return array('z');
    }

    protected function getValidAllowAddFormattedChoicesForValues()
    {
        return array(
            0 => array('value' => 'z', 'label' => 'z'),
        );
    }

    protected function getValidPaginationFormattedChoices()
    {
        return array();
    }
}
