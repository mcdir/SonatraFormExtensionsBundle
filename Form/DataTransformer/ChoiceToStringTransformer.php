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
class ChoiceToStringTransformer implements DataTransformerInterface
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
     * @param string $value
     *
     * @return string
     */
    public function transform($value)
    {
        return (string) current($this->choiceList->getValuesForChoices(array($value)));
    }

    /**
     * @param string $value
     *
     * @return string
     *
     * @throws TransformationFailedException If the given value is not an string
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
        if (('' === $value || null === $value) && !$this->required) {
            return null;
        }

        $choices = $this->choiceList->getChoicesForValues(array($value));

        if (1 !== count($choices)) {
            throw new TransformationFailedException(sprintf('The choice "%s" does not exist or is not unique', $value));
        }

        $choice = current($choices);

        return '' === $choice ? null : $choice;
    }
}
