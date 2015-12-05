<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\Type;

use Sonatra\Bundle\FormExtensionsBundle\Form\Type\CurrencyType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Intl\Intl;

/**
 * Tests case for currency type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CurrencyTypeTest extends TypeTestCase
{
    public function testValidChoiceList()
    {
        $type = new CurrencyType();
        $form = $this->factory->create($type);
        /* @var ArrayChoiceList $list */
        $list = $form->getConfig()->getOption('choice_list');

        $this->assertInstanceOf('Symfony\Component\Form\ChoiceList\ArrayChoiceList', $list);

        $validList = Intl::getCurrencyBundle()->getCurrencyNames('en');
        sort($validList);

        $choices = $list->getChoices();
        sort($choices);

        $this->assertSame($validList, $choices);
    }
}
