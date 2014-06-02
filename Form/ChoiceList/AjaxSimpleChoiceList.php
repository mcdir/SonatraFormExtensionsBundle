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

use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxSimpleChoiceList extends SimpleChoiceList implements AjaxChoiceListInterface
{
    /**
     * @var boolean
     */
    private $allowAdd;

    /**
     * @var boolean
     */
    private $ajax;

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
     * @var array
     */
    private $cacheChoices;

    /**
     * @var array
     */
    private $cacheFilteredChoices;

    /**
     * Creates a new ajax simple choice list.
     *
     * @param array $choices          The array of choices with the choices as keys and
     *                                the labels as values. Choices may also be given
     *                                as hierarchy of unlimited depth by creating nested
     *                                arrays. The title of the sub-hierarchy is stored
     *                                in the array key pointing to the nested array.
     * @param array $preferredChoices A flat array of choices that should be
     *                                presented to the user with priority.
     */
    public function __construct($choices, array $preferredChoices = array())
    {
        $this->allowAdd = false;
        $this->ajax = false;
        $this->pageSize = 10;
        $this->pageNumber = 1;
        $this->search = '';
        $this->ids = array();

        parent::__construct($choices, $preferredChoices);
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices()
    {
        if (!$this->ajax) {
            return parent::getChoices();
        }

        if (null !== $this->cacheChoices) {
            return $this->cacheChoices;
        }

        $this->cacheChoices = array();
        $startTo = ($this->getPageNumber() - 1) * $this->getPageSize();
        $endTo = $startTo + $this->getPageSize();

        $choices = $this->getFilteredChoices();

        if ($endTo > $this->getSize()) {
            $endTo = $this->getSize();
        }

        if (count($choices) > 0 && is_int(array_keys($choices)[0])) {
            for ($index=$startTo; $index<$endTo; $index++) {
                $choice = $choices[$index];

                $this->cacheChoices[] = array(
                    'id'   => (string) $choice->value,
                    'text' => $choice->label,
                );
            }

            return $this->cacheChoices;
        }

        $index = 0;

        foreach ($choices as $groupName => $subChoices) {
            $group = array(
                'text'     => $groupName,
                'children' => array(),
            );

            foreach ($subChoices as $choice) {
                if ($index >= $startTo && $index <= $endTo) {
                    $group['children'][] = array(
                        'id'   => (string) $choice->value,
                        'text' => $choice->label,
                    );
                }

                $index++;
            }

            if (count($group['children']) > 0) {
                $this->cacheChoices[] = $group;
            }
        }

        return $this->cacheChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelChoicesForValues(array $values)
    {
        $choices = array();
        $data = array();

        foreach ($this->getChoiceViews() as $choice) {
            // group choice
            if (is_array($choice)) {
                foreach ($choice as $subChoice) {
                    $data[] = $subChoice->value;
                    $this->extractLabel($choices, $subChoice, $values);
                }

            } else {
                $data[] = $choice->value;
                $this->extractLabel($choices, $choice, $values);
            }
        }

        foreach ($values as $value) {
            if (!in_array($value, $data)) {
                $choices[] = array(
                    'id'   => $value,
                    'text' => $value,
                );
            }
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoicesForValues(array $values)
    {
        if (!$this->allowAdd || empty($values) || (1 === count($values) && '' === $values[0])) {
            return parent::getChoicesForValues($values);
        }

        $items = parent::getChoicesForValues($values);
        $choices = array();

        foreach ($values as $value) {
            $pos = array_search($value, $items);

            if (false !== $pos) {
                $choices[] = $items[$pos];

            } else {
                $choices[] = $value;
            }
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataChoices()
    {
        if ($this->ajax) {
            return array();
        }

        $choices = $this->getChoiceViews();
        $data = array();

        /* @var ChoiceView $choice */
        foreach ($choices as $index => $choice) {
            // group choice
            if (is_array($choice)) {
                $children = array();

                foreach ($choice as $subChoice) {
                    $this->extractLabel($children, $subChoice, array($choice->value));
                }

                $data[] = array(
                    'text'     => $index,
                    'children' => $children,
                );

            } else {
                $this->extractLabel($data, $choice, array($choice->value));
            }
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowAdd($allowAdd)
    {
        $this->cacheChoices = null;
        $this->cacheFilteredChoices = null;
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
    public function setAjax($ajax)
    {
        $this->cacheChoices = null;
        $this->cacheFilteredChoices = null;
        $this->ajax = $ajax;
    }

    /**
     * {@inheritdoc}
     */
    public function getAjax()
    {
        return $this->ajax;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtractValues($extractValues)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        $filteredChoices = $this->getFilteredChoices();

        if (count($filteredChoices) > 0 && is_int(array_keys($filteredChoices)[0])) {
            return count($filteredChoices);
        }

        $size = 0;

        /* @var ChoiceView[] $choices */
        foreach ($filteredChoices as $choices) {
            $size += count($choices);
        }

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageSize($size)
    {
        $this->cacheChoices = null;
        $this->cacheFilteredChoices = null;
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
        $this->cacheChoices = null;
        $this->cacheFilteredChoices = null;
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
        $this->cacheChoices = null;
        $this->cacheFilteredChoices = null;
        $this->search = $search;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearch()
    {
        $this->cacheChoices = null;
        $this->cacheFilteredChoices = null;

        return $this->search;
    }

    /**
     * {@inheritdoc}
     */
    public function setIds(array $ids)
    {
        $this->cacheChoices = null;
        $this->cacheFilteredChoices = null;
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
     * Get the filtered choices.
     *
     * @return ChoiceView[]
     */
    protected function getFilteredChoices()
    {
        if (null === $this->search || '' === $this->search) {
            return $this->getChoiceViews();
        }

        if (null !== $this->cacheFilteredChoices) {
            return $this->cacheFilteredChoices;
        }

        $this->cacheFilteredChoices = array();
        $choices = $this->getChoiceViews();

        if (count($choices) > 0 && is_int(array_keys($choices)[0])) {
            foreach ($choices as $choice) {
                if (false !== stripos($choice->label, $this->search) && !in_array($choice->value, $this->getIds())) {
                    $this->cacheFilteredChoices[] = $choice;
                }
            }

            return $this->cacheFilteredChoices;
        }

        foreach ($choices as $groupName => $subChoices) {
            $group = array();

            foreach ($subChoices as $choice) {
                if (false !== stripos($choice->label, $this->search) && !in_array($choice->value, $this->getIds())) {
                    $group[] = $choice;
                }
            }

            if (count($group) > 0) {
                $this->cacheFilteredChoices[$groupName] = $group;
            }
        }

        return $this->cacheFilteredChoices;
    }

    /**
     * Extract the choice label.
     *
     * @param array      $result The array of list extraction
     * @param ChoiceView $choice
     * @param array      $values
     */
    protected function extractLabel(array &$result, ChoiceView $choice, array $values)
    {
        if (in_array($choice->data, $values)) {
            $result[] = array(
                'id'   => (string) $choice->value,
                'text' => $choice->label
            );
        }
    }

    /**
     * Gets all choice views, including preferred and remaining views.
     *
     * @return array<int, ChoiceView>|array<string, ChoiceView[]>
     */
    protected function getChoiceViews()
    {
        $choices = array_merge_recursive($this->getPreferredViews(), $this->getRemainingViews());

        if (count($choices) > 0 && !is_int(array_keys($choices)[0])) {
            foreach ($choices as $group => $subChoices) {
                $choices[$group] = array_values($subChoices);
            }
        }

        return $choices;
    }
}
