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
class ChoiceToValueTransformer implements DataTransformerInterface
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
     * @param string $choice
     *
     * @return string
     */
    public function transform($choice)
    {
        return (string) current($this->choiceList->getValuesForChoices(array($choice)));
    }

    /**
     * @param string $value
     *
     * @return string
     *
     * @throws TransformationFailedException If the given value is not an array
     *                                       or if no matching choice could be
     *                                       found for some given value.
     */
    public function reverseTransform($value)
    {
        if (null !== $value && !is_scalar($value)) {
            throw new TransformationFailedException('Expected a scalar.');
        }

        $value = $this->reverseTransformEmptyValue($value);

        return $this->reverseTransformChoice($value);
    }

    /**
     * Reverse transform the empty value.
     *
     * @param string|null $value
     *
     * @return string|null
     *
     * @throws TransformationFailedException When value is empty and required
     */
    protected function reverseTransformEmptyValue($value)
    {
        // These are now valid ChoiceList values, so we can return null
        // right away
        if ('' === $value) {
            $value = null;
        }

        if (null === $value) {
            if (!$this->required) {
                return null;

            } else {
                throw new TransformationFailedException('Value is required.');
            }
        }

        return $value;
    }

    /**
     * Reverse transform the choice.
     *
     * @param string|null $value
     *
     * @return string|null
     *
     * @throws TransformationFailedException
     */
    protected function reverseTransformChoice($value)
    {
        $choices = $this->choiceList->getChoicesForValues(array($value));

        if (1 !== count($choices)) {
            throw new TransformationFailedException(sprintf('The choice "%s" does not exist or is not unique', $value));
        }

        $choice = current($choices);

        return '' === $choice ? null : $choice;
    }
}
