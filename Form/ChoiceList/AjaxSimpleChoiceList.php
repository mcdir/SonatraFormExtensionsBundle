<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxSimpleChoiceList extends SimpleChoiceList implements AjaxChoiceListInterface
{
    /**
     * @var AjaxChoiceListFormatterInterface
     */
    private $formatter;

    /**
     * @var boolean
     */
    private $allowAdd;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @var string
     */
    private $search;

    /**
     * @var array
     */
    private $ids;

    /**
     * @var int
     */
    private $size;

    /**
     * @var array
     */
    private $backupChoices;

    /**
     * @var array
     */
    private $backupPreferredChoices;

    /**
     * Creates a new ajax simple choice list.
     *
     * @param AjaxChoiceListFormatterInterface $formatter        The formatter
     * @param array                            $choices          The array of choices with the choices as keys and
     *                                                           the labels as values. Choices may also be given
     *                                                           as hierarchy of unlimited depth by creating nested
     *                                                           arrays. The title of the sub-hierarchy is stored
     *                                                           in the array key pointing to the nested array.
     * @param array                            $preferredChoices A flat array of choices that should be
     *                                                           presented to the user with priority.
     */
    public function __construct(AjaxChoiceListFormatterInterface $formatter, $choices, array $preferredChoices = array())
    {
        $this->formatter = $formatter;
        $this->allowAdd = false;
        $this->ajax = false;
        $this->pageSize = 10;
        $this->pageNumber = 1;
        $this->search = '';
        $this->ids = array();
        $this->backupChoices = $choices;
        $this->backupPreferredChoices = $preferredChoices;

        parent::__construct($choices, $preferredChoices);
    }

    /**
     * {@inheritdoc}
     */
    public function getChoicesForValues(array $values)
    {
        $choices = parent::getChoicesForValues($values);

        if ($this->getAllowAdd()) {
            $items = $choices;

            foreach ($values as $value) {
                $pos = array_search($value, $items);

                if (false === $pos) {
                    $choices[] = $value;
                }
            }
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesForChoices(array $values)
    {
        $parentValues = parent::getValuesForChoices($values);

        if ($this->getAllowAdd()) {
            $items = $parentValues;

            foreach ($values as $value) {
                $pos = array_search($value, $items);

                if (false === $pos) {
                    $parentValues[] = $value;
                }
            }
        }

        return $parentValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedChoicesForValues(array $values)
    {
        $choices = array();
        $unresolvedValues = $values;

        /* @var ChoiceView $choice */
        foreach ($this->getChoiceViews() as $choice) {
            // group choice
            if (is_array($choice)) {
                /* @var ChoiceView $subChoice */
                foreach ($choice as $subChoice) {
                    if (in_array($subChoice->value, $values)) {
                        $choices[] = $this->formatter->formatChoice($subChoice);
                        $pos = array_search($subChoice->value, $unresolvedValues);
                        array_splice($unresolvedValues, $pos, 1);
                    }
                }

            } else {
                if (in_array($choice->value, $values)) {
                    $choices[] = $this->formatter->formatChoice($choice);
                    $pos = array_search($choice->value, $unresolvedValues);
                    array_splice($unresolvedValues, $pos, 1);
                }
            }
        }

        foreach ($unresolvedValues as $value) {
            $choices[] = $this->formatter->formatChoice(new ChoiceView($value, $value, $value));
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedChoices()
    {
        $choices = $this->getChoiceViews();
        $formattedChoices = array();
        $startTo = ($this->getPageNumber() - 1) * $this->getPageSize();
        $endTo = $startTo + $this->getPageSize();

        if ($endTo > $this->getSize()) {
            $endTo = $this->getSize();
        }

        // simple
        if (count($choices) > 0 && is_int(array_keys($choices)[0])) {
            for ($index=$startTo; $index<$endTo; $index++) {
                $formattedChoices[] = $this->formatter->formatChoice($choices[$index]);
            }

            return $formattedChoices;
        }

        // group
        $index = 0;

        foreach ($choices as $groupName => $groupChoices) {
            $group = $this->formatter->formatGroupChoice($groupName);

            foreach ($groupChoices as $subChoice) {
                if ($index >= $startTo && $index < $endTo) {
                    $group = $this->formatter->addChoiceInGroup($group, $subChoice);
                }

                $index++;
            }

            if (!$this->formatter->isEmptyGroup($group)) {
                $formattedChoices[] = $group;
            }
        }

        return $formattedChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstChoiceView()
    {
        $choices = $this->getChoiceViews();

        if (count($choices) > 0) {
            $firstChoice = $choices[array_keys($choices)[0]];

            if ($firstChoice instanceof ChoiceView) {
                return $firstChoice;
            }

            // group
            if (is_array($firstChoice) && isset($firstChoice[0])) {
                return $firstChoice[0];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowAdd($allowAdd)
    {
        $this->allowAdd = $allowAdd;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowAdd()
    {
        return $this->allowAdd;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageSize($size)
    {
        $this->pageSize = $size;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageNumber($number)
    {
        $this->pageNumber = $number;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * {@inheritdoc}
     */
    public function setIds(array $ids)
    {
        $this->ids = $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $filteredChoices = array();

        if (null === $this->search || '' === $this->search) {
            $filteredChoices = $this->backupChoices;

        } else {
            foreach ($this->backupChoices as $group => $choice) {
                if (is_array($choice)) {
                    foreach ($choice as $id => $subChoice) {
                        if (false !== stripos($subChoice, $this->search) && !in_array($subChoice, $this->getIds())) {
                            if (!array_key_exists($group, $filteredChoices)) {
                                $filteredChoices[$group] = array();
                            }

                            $filteredChoices[$group][$id] = $subChoice;
                        }
                    }

                } else {
                    if (false !== stripos($choice, $this->search) && !in_array($choice, $this->getIds())) {
                        $filteredChoices[$group] = $choice;
                    }
                }
            }
        }

        $this->initialize($filteredChoices, $filteredChoices, array_flip($this->backupPreferredChoices));
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize($choices, array $labels, array $preferredChoices)
    {
        parent::initialize($choices, $labels, $preferredChoices);

        $choices = $this->getChoices();
        $this->size = count($choices);

        // group
        if ($this->size > 0 && is_array($choices[array_keys($choices)[0]])) {
            $this->size = 0;

            foreach ($choices as $subChoices) {
                $this->size += count($subChoices);
            }
        }
    }

    /**
     * Gets all choice views, including preferred and remaining views.
     *
     * @return ChoiceView[]|array<string, ChoiceView[]>
     */
    protected function getChoiceViews()
    {
        return array_merge_recursive($this->getPreferredViews(), $this->getRemainingViews());
    }
}
