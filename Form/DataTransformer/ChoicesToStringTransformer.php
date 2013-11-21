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
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoicesToStringTransformer implements DataTransformerInterface
{
    /**
     * @var ChoiceListInterface
     */
    private $choiceList;

    /**
     * @var boolean
     */
    private $required;

    /**
     * Constructor.
     *
     * @param ChoiceListInterface $choiceList
     * @param boolean             $required
     */
    public function __construct(ChoiceListInterface $choiceList, $required = false)
    {
        $this->choiceList = $choiceList;
        $this->required = $required;
    }

    /**
     * @param array $value
     *
     * @return array
     *
     * @throws TransformationFailedException If the given value is not an array.
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_array($value)) {
            throw new TransformationFailedException('Expected an array.');
        }

        return implode(',', $this->choiceList->getValuesForChoices($value));
    }

    /**
     * @param array $value
     *
     * @return array
     *
     * @throws TransformationFailedException If the given value is not an string
     *                                       or if no matching choice could be
     *                                       found for some given value.
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            $value = array('');
        }

        if (1 === count($value)) {
            $value = $value[0];
        }

        if (!is_string($value)) {
            throw new TransformationFailedException('Expected an string.');
        }

        if ('' === $value && !$this->required) {
            return array();
        }

        $value = explode(',', $value);
        $choices = $this->choiceList->getChoicesForValues($value);

        if (count($choices) !== count($value)) {
            throw new TransformationFailedException('Could not find all matching choices for the given values');
        }

        return $choices;
    }
}
