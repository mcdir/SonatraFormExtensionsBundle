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
        $form = $this->factory->create(CurrencyType::class);

        $validList = array_keys(Intl::getCurrencyBundle()->getCurrencyNames('en'));
        sort($validList);

        $choices = $form->getConfig()->getOption('choices');
        sort($choices);

        $this->assertSame($validList, $choices);
    }
}
