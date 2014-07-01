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
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoicesToValuesTransformer implements DataTransformerInterface
{
    /**
     * @var AjaxChoiceListInterface
     */
    private $choiceList;

    /**
     * @var boolean
     */
    private $required;

    /**
     * Constructor.
     *
     * @param AjaxChoiceListInterface $choiceList
     * @param boolean                 $required
     */
    public function __construct(AjaxChoiceListInterface $choiceList, $required = true)
    {
        $this->choiceList = $choiceList;
        $this->required = $required;
    }

    /**
     * @param array $array
     *
     * @return array
     *
     * @throws TransformationFailedException If the given value is not an array.
     */
    public function transform($array)
    {
        if (null === $array) {
            return array();
        }

        if (!is_array($array)) {
            throw new TransformationFailedException('Expected an array.');
        }

        return $this->choiceList->getValuesForChoices($array);
    }

    /**
     * @param array $array
     *
     * @return array
     *
     * @throws TransformationFailedException If the given value is not an array
     *                                       or if no matching choice could be
     *                                       found for some given value.
     */
    public function reverseTransform($array)
    {
        if (null === $array) {
            $array = array();
        }

        if (!is_array($array)) {
            throw new TransformationFailedException('Expected an array.');
        }

        if ($this->required && empty($array)) {
            throw new TransformationFailedException('Values is required.');
        }

        $choices = $this->choiceList->getChoicesForValues($array);

        if (count($choices) !== count($array)) {
            throw new TransformationFailedException('Could not find all matching choices for the given values');
        }

        return $choices;
    }
}
