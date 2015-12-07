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
 * Tests case for language of select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class LanguageSelect2TypeExtensionTest extends AbstractSelect2TypeExtensionTest
{
    protected function getExtensionTypeName()
    {
        return LanguageType::class;
    }

    protected function getSingleData()
    {
        return 'fr';
    }

    protected function getValidSingleValue()
    {
        return 'fr';
    }

    protected function getValidAjaxSingleValue()
    {
        return 'fr';
    }

    protected function getMultipleData()
    {
        return array('fr', 'en');
    }

    protected function getValidMultipleValue()
    {
        return array('fr', 'en');
    }

    protected function getValidAjaxMultipleValue()
    {
        return $this->getValidMultipleValue();
    }
}
