<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Symfony\Component\Form\ChoiceList\View\ChoiceGroupView;
use Symfony\Component\Form\ChoiceList\View\ChoiceView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AjaxChoiceListFormatterInterface
{
    /**
     * Format the ajax response data.
     *
     * @param AjaxChoiceLoaderInterface $choiceLoader The choice loader
     *
     * @return array The formatted ajax data
     */
    public function formatResponseData(AjaxChoiceLoaderInterface $choiceLoader);

    /**
     * Formats the choice view to AJAX format.
     *
     * @param ChoiceView $choice
     *
     * @return mixed
     */
    public function formatChoice(ChoiceView $choice);

    /**
     * Formats the group choice view to AJAX format.
     *
     * @param ChoiceGroupView $choiceGroup The choice group
     *
     * @return mixed
     */
    public function formatGroupChoice(ChoiceGroupView $choiceGroup);

    /**
     * @param mixed      $group  The group choice formatted
     * @param ChoiceView $choice The child choice view
     *
     * @return mixed The group with the new choice formatted
     */
    public function addChoiceInGroup($group, ChoiceView $choice);

    /**
     * Checks if the group is empty.
     *
     * @param mixed $group The group choice formatted
     *
     * @return bool
     */
    public function isEmptyGroup($group);
}
