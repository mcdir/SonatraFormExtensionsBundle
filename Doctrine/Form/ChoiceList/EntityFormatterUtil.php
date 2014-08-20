<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\FormatterUtil;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class EntityFormatterUtil extends FormatterUtil
{
    /**
     * {@inheritdoc}
     */
    protected function getSimpleFormattedChoices($choices)
    {
        $formattedChoices = array();

        foreach ($choices as $choice) {
            $formattedChoices[] = $this->formatter->formatChoice($choice);
        }

        return $formattedChoices;
    }

    /**
     * {@inheritdoc}
     */
    protected function getGroupFormattedChoices($choices)
    {
        $formattedChoices = array();

        foreach ($choices as $groupName => $groupChoices) {
            $group = $this->formatter->formatGroupChoice($groupName);

            foreach ($groupChoices as $subChoice) {
                $group = $this->formatter->addChoiceInGroup($group, $subChoice);
            }

            if (!$this->formatter->isEmptyGroup($group)) {
                $formattedChoices[] = $group;
            }
        }

        return $formattedChoices;
    }
}
