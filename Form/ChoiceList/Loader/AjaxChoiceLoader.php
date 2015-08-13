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

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\Traits\AjaxLoaderTrait;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxChoiceLoader extends DynamicChoiceLoader implements AjaxChoiceLoaderInterface
{
    use AjaxLoaderTrait;

    /**
     * @var array
     */
    protected $filteredChoices;

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
        parent::__construct($choices, $choiceAsValues, $factory);

        $this->initAjax();
        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function loadPaginatedChoiceList($value = null)
    {
        $choices = LoaderUtil::paginateChoices($this, $this->filteredChoices);

        return $this->createChoiceList($choices, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceListForView(array $values, $value = null)
    {
        $choices = $this->getSelectedChoices($values, $value);

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

        return $this->choiceList = $this->createChoiceList($this->filteredChoices, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        if (null === $this->search || '' === $this->search) {
            $filteredChoices = $this->choices;
        } else {
            $filteredChoices = $this->resetSearchChoices();
        }

        $this->initialize($filteredChoices);

        return $this;
    }

    /**
     * Reset the choices for search.
     *
     * @return array The filtered choices
     */
    protected function resetSearchChoices()
    {
        $filteredChoices = array();

        foreach ($this->choices as $group => $choice) {
            if (is_array($choice)) {
                foreach ($choice as $key => $subChoice) {
                    list($id, $label) = $this->getIdAndLabel($key, $subChoice);

                    if (false !== stripos($label, $this->search) && !in_array($id, $this->getIds())) {
                        if (!array_key_exists($group, $filteredChoices)) {
                            $filteredChoices[$group] = array();
                        }

                        $filteredChoices[$group][$key] = $subChoice;
                    }
                }
            } else {
                list($id, $label) = $this->getIdAndLabel($group, $choice);

                if (false !== stripos($label, $this->search) && !in_array($id, $this->getIds())) {
                    $filteredChoices[$group] = $choice;
                }
            }
        }

        return $filteredChoices;
    }

    /**
     * Get the id and label of original choices.
     *
     * @param string $key   The key of array
     * @param string $value The value of array
     *
     * @return array The id and label
     */
    protected function getIdAndLabel($key, $value)
    {
        return $this->choiceAsValues
            ? array($value, $key)
            : array($key, $value);
    }

    /**
     * @param array $choices The choices
     */
    protected function initialize($choices)
    {
        parent::initialize($choices);

        $this->filteredChoices = $choices;
        $this->choiceList = null;
    }

    /**
     * Keep only the selected values in choices.
     *
     * @param array         $values The selected values
     * @param null|callable $value  The callable function
     *
     * @return array The selected choices
     */
    protected function getSelectedChoices(array $values, $value = null)
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
                    if (in_array($choiceValue, $values)) {
                        $key = $this->choiceAsValues ? $choiceKey : $choiceValue;
                        $choices[$group][$key] = $this->choiceAsValues ? $choiceValue : $choiceKey;
                        $allChoices[$key] = $this->choiceAsValues ? $choiceValue : $choiceKey;
                    }
                }
            } elseif (in_array($choice, $values)) {
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
}
