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
class Select2AjaxChoiceListFormatter implements AjaxChoiceListFormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function formatChoice(ChoiceView $choice)
    {
        return array(
            'id'   => $choice->value,
            'text' => $choice->label,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function formatGroupChoice($name)
    {
        return array(
            'text'     => $name,
            'children' => array(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addChoiceInGroup($group, ChoiceView $choice)
    {
        $group['children'][] = $this->formatChoice($choice);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmptyGroup($group)
    {
        return count($group['children']) === 0;
    }
}
