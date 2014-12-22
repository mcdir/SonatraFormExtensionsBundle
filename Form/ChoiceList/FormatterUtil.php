<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FormatterUtil
{
    /**
     * @var AjaxChoiceListInterface
     */
    protected $choiceList;

    /**
     * @var AjaxChoiceListFormatterInterface
     */
    protected $formatter;

    public function __construct(AjaxChoiceListInterface $choiceList, AjaxChoiceListFormatterInterface $formatter)
    {
        $this->choiceList = $choiceList;
        $this->formatter = $formatter;
    }

    /**
     * Gets the formatted choices.
     *
     * @return array The list or group list of formatted choices
     */
    public function getFormattedChoices()
    {
        $choices = array_merge_recursive($this->choiceList->getPreferredViews(), $this->choiceList->getRemainingViews());
        $keyChoices = array_keys($choices);

        // simple
        if (count($choices) > 0 && is_int($keyChoices[0])) {
            return $this->getSimpleFormattedChoices($choices);
        }

        return $this->getGroupFormattedChoices($choices);
    }

    /**
     * Gets range values.
     *
     * @return integer[] The startTo and endTo
     */
    protected function getRangeValues()
    {
        $startTo = ($this->choiceList->getPageNumber() - 1) * $this->choiceList->getPageSize();
        $endTo = $startTo + $this->choiceList->getPageSize();

        if (0 >= $this->choiceList->getPageSize()) {
            $endTo = $this->choiceList->getSize();
        }

        if ($endTo > $this->choiceList->getSize()) {
            $endTo = $this->choiceList->getSize();
        }

        return array($startTo, $endTo);
    }

    /**
     * Gets the simple formatted choices.
     *
     * @param array|ChoiceView[] $choices
     *
     * @return array The list or group list of formatted choices
     */
    protected function getSimpleFormattedChoices($choices)
    {
        $formattedChoices = array();
        list($startTo, $endTo) = $this->getRangeValues();

        for ($index = $startTo; $index<$endTo; $index++) {
            $formattedChoices[] = $this->formatter->formatChoice($choices[$index]);
        }

        return $formattedChoices;
    }

    /**
     * Gets the group formatted choices.
     *
     * @param array|ChoiceView[] $choices
     *
     * @return array The list or group list of formatted choices
     */
    protected function getGroupFormattedChoices($choices)
    {
        $index = 0;
        $formattedChoices = array();
        list($startTo, $endTo) = $this->getRangeValues();

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
}
