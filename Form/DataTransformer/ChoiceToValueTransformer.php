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
    private $allowAdd;

    /**
     * @var boolean
     */
    private $required;

    /**
     * Constructor.
     *
     * @param AjaxChoiceListInterface $choiceList
     * @param boolean                 $allowAdd
     * @param boolean                 $required
     */
    public function __construct(AjaxChoiceListInterface $choiceList, $allowAdd = false, $required = true)
    {
        $this->choiceList = $choiceList;
        $this->allowAdd = $allowAdd;
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

        if ($this->allowAdd) {
            return $value;
        }

        $choices = $this->choiceList->getChoicesForValues(array($value));

        if (1 !== count($choices)) {
            throw new TransformationFailedException(sprintf('The choice "%s" does not exist or is not unique', $value));
        }

        $choice = current($choices);

        return '' === $choice ? null : $choice;
    }
}
