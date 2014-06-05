<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter;

use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AjaxChoiceListFormatterInterface
{
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
     * @param string $name
     *
     * @return mixed
     */
    public function formatGroupChoice($name);

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
