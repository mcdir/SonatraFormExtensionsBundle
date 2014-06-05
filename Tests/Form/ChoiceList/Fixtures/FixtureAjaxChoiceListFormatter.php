<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FixtureAjaxChoiceListFormatter implements AjaxChoiceListFormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function formatChoice(ChoiceView $choice)
    {
        return array(
            'value' => $choice->value,
            'label' => $choice->label,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function formatGroupChoice($name)
    {
        return array(
            'label'   => $name,
            'choices' => array(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addChoiceInGroup($group, ChoiceView $choice)
    {
        $group['choices'][] = $this->formatChoice($choice);

        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function isEmptyGroup($group)
    {
        return count($group['choices']) === 0;
    }
}
