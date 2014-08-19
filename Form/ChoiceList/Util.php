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

use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Util
{
    /**
     * Gets the first choice view.
     *
     * @param ChoiceView[]|array<string, ChoiceView[]> $choices
     *
     * @return ChoiceView|null
     */
    public static function getFirstChoiceView($choices)
    {
        $firstChoice = null;
        $keyChoices = array_keys($choices);

        if (count($choices) > 0) {
            $firstChoice = $choices[$keyChoices[0]];

            // group
            if (is_array($firstChoice) && count($firstChoice) > 0) {
                $keyFirstChoice = array_keys($firstChoice);
                $firstChoice = $firstChoice[$keyFirstChoice[0]];
            }
        }

        return $firstChoice;
    }
}
