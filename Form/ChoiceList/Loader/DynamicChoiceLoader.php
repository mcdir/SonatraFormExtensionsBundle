<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader;

use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DynamicChoiceLoader extends AbstractDynamicChoiceLoader
{
    /**
     * @var array
     */
    protected $choices;

    /**
     * @var bool
     */
    protected $choiceAsValues;

    /**
     * @var int|null
     */
    protected $size;

    /**
     * Creates a new choice loader.
     *
     * @param array                           $choices        The choices
     * @param bool                            $choiceAsValues Check if the values are the keys in choices
     * @param ChoiceListFactoryInterface|null $factory        The factory for creating
     *                                                        the loaded choice list
     */
    public function __construct(array $choices, $choiceAsValues = false, $factory = null)
    {
        parent::__construct($factory);

        $this->choices = $choices;
        $this->choiceAsValues = $choiceAsValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if (null === $this->size) {
            $this->initialize($this->choices);
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceListForView(array $values, $value = null)
    {
        $choices = $this->getChoices($values, $value);

        return $this->createChoiceList($choices, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceList($value = null)
    {
        if ($this->choiceList) {
            return $this->choiceList;
        }

        return $this->choiceList = $this->createChoiceList($this->choices, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        // Performance optimization
        if (empty($values)) {
            return array();
        }

        return $this->addNewValues($this->loadChoiceList($value)
            ->getChoicesForValues($values), $values);
    }

    /**
     * {@inheritdoc}
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        // Performance optimization
        if (empty($choices)) {
            return array();
        }

        return $this->addNewValues($this->loadChoiceList($value)
            ->getValuesForChoices($choices), $choices);
    }

    /**
     * Add new values.
     *
     * @param array $selections The list of selection
     * @param array $values     The list of value
     *
     * @return array The list of new selection
     */
    protected function addNewValues(array $selections, array $values)
    {
        if ($this->isAllowAdd()) {
            foreach ($values as $value) {
                if (!in_array($value, $selections) && !in_array((string) $value, $selections)) {
                    $selections[] = (string) $value;
                }
            }
        }

        return $selections;
    }

    /**
     * Create the choice list.
     *
     * @param array         $choices The choices
     * @param null|callable $value   The callable function
     *
     * @return ChoiceListInterface
     */
    protected function createChoiceList(array $choices, $value = null)
    {
        if (!$this->choiceAsValues) {
            return $this->factory->createListFromFlippedChoices($choices, $value);
        }

        return $this->factory->createListFromChoices($choices, $value);
    }

    /**
     * @param array $choices The choices
     */
    protected function initialize($choices)
    {
        $this->size = count($choices);

        // group
        if ($this->size > 0 && is_array(current($choices))) {
            $this->size = 0;

            foreach ($choices as $subChoices) {
                $this->size += count($subChoices);
            }
        }
    }

    /**
     * Keep only the values in choices.
     *
     * @param array         $values The selected values
     * @param null|callable $value  The callable function
     *
     * @return array The choices
     */
    protected function getChoices(array $values, $value = null)
    {
        $structuredValues = $this->loadChoiceList($value)->getStructuredValues();
        $values = $this->forceStringValues($values);
        $allChoices = array();
        $choices = array();
        $isGrouped = false;

        foreach ($structuredValues as $group => $choice) {
            // group
            if (is_array($choice)) {
                $isGrouped = true;
                foreach ($choice as $choiceKey => $choiceValue) {
                    $key = $this->choiceAsValues ? $choiceKey : $choiceValue;
                    $choices[$group][$key] = $this->choiceAsValues ? $choiceValue : $choiceKey;
                    $allChoices[$key] = $this->choiceAsValues ? $choiceValue : $choiceKey;
                }
            } else {
                $key = $this->choiceAsValues ? $group : $choice;
                $choices[$key] = $this->choiceAsValues ? $choice : $group;
                $allChoices[$key] = $this->choiceAsValues ? $choice : $group;
            }
        }

        if ($this->isAllowAdd()) {
            $choices = $this->addNewTagsInChoices($choices, $allChoices, $values, $isGrouped);
        }

        return $choices;
    }

    /**
     * Force value with string type.
     *
     * @param array $values
     *
     * @return string[]
     */
    protected function forceStringValues(array $values)
    {
        $size = count($values);

        for ($i = 0; $i < $size; ++$i) {
            $values[$i] = (string) $values[$i];
        }

        return $values;
    }

    /**
     * Add new tags in choices.
     *
     * @param array $choices    The choices
     * @param array $allChoices The all choices
     * @param array $values     The values
     * @param bool  $isGrouped  Check if the choices is grouped
     *
     * @return array The choice with new tags
     */
    protected function addNewTagsInChoices(array $choices, array $allChoices, array $values, $isGrouped)
    {
        foreach ($values as $value) {
            if (($this->choiceAsValues && !in_array($value, $allChoices))
                    || (!$this->choiceAsValues && !isset($allChoices[$value]))) {
                if ($isGrouped) {
                    $choices['-------'][$value] = $value;
                } else {
                    $choices[$value] = $value;
                }
            }
        }

        return $choices;
    }
}
