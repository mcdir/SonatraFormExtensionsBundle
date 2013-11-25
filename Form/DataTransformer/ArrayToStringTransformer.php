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
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Transforms between a array and a string.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ArrayToStringTransformer implements DataTransformerInterface
{
    /**
     * @var boolean
     */
    private $required;

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
     * Transforms a Array into a string.
     *
     * @param array $value Array value.
     *
     * @return string String value.
     *
     * @throws TransformationFailedException If the given value is not a Array.
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException('Expected a Array.');
        }

        return implode(',', $value);
    }

    /**
     * Transforms a string into a Array.
     *
     * @param string $value String value.
     *
     * @return array Array value.
     *
     * @throws TransformationFailedException If the given value is not a string.
     */
    public function reverseTransform($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected an string or array.');
        }

        if ('' === $value && !$this->required) {
            return array();
        }

        return explode(',', $value);
    }
}
