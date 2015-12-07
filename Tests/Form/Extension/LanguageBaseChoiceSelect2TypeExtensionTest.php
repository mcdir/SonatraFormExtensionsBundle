<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\LanguageType;

/**
 * Tests case for language of base choice select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class LanguageBaseChoiceSelect2TypeExtensionTest extends AbstractBaseChoiceSelect2TypeExtensionTest
{
    protected function getExtensionTypeName()
    {
        return LanguageType::class;
    }
}
