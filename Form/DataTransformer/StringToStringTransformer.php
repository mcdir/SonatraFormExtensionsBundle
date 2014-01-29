<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class StringToStringTransformer implements DataTransformerInterface
{
    protected $required;

    /**
     * Constructor.
     *
     * @param boolean $required
     */
    public function __construct($required = false)
    {
        $this->required = $required;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function transform($string)
    {
        return $string;
    }

    /**
     * @param string $string
     *
     * @return array
     */
    public function reverseTransform($string)
    {
        // force form error with required option
        if ($this->required && (null === $string || '' === $string)) {
            return '*';
        }

        return $string;
    }
}
