<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\Converter;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface NewTagConverterInterface
{
    /**
     * Convert the form value to doctrine object.
     *
     * @param string $value The form value
     *
     * @return object The doctrine object
     */
    public function convert($value);
}
