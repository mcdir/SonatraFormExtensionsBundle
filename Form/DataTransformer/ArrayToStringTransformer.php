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
class ArrayToStringTransformer implements DataTransformerInterface
{
    protected $required;
    protected $compound;

    /**
     * Constructor.
     *
     * @param boolean $required
     */
    public function __construct($required = false, $compound = true)
    {
        $this->required = $required;
        $this->compound = $compound;
    }

    /**
     * @param array $array
     *
     * @return string
     */
    public function transform($array)
    {
        if (null === $array) {
            return '';
        }

        return implode(',', array_unique($array));
    }

    /**
     * @param string $string
     *
     * @return array
     */
    public function reverseTransform($string)
    {
        if ($this->compound) {
            return $string;
        }

        if (is_array($string) && 1 === count($string)) {
            $string = $string[0];
        }

        // force form error with required option
        if ($this->required && (null === $string || '' === $string)) {
            return array('*');

        } elseif (null === $string || '' === $string) {
            return array();
        }

        return explode(',', $string);
    }
}
