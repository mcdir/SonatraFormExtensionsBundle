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
            $firstChoice = self::getFirstValue($firstChoice, false, $firstChoice);
        }

        return $firstChoice;
    }

    /**
     * Gets the first value.
     *
     * @param array|mixed $data
     * @param bool        $singleString
     * @param mixed|null  $default
     *
     * @return string|mixed|null
     */
    public static function getFirstValue($data, $singleString = false, $default = null)
    {
        if (is_array($data) && count($data) > 0) {
            $firstKey = array_keys($data);
            $value = $data[$firstKey[0]];

            if (!$singleString || ($singleString && 1 === count($data) && is_string($value))) {
                $default = $value;
            }
        }

        return $default;
    }

    /**
     * Finds the items for types.
     *
     * @param array $choices     The choices
     * @param array $parents     The parents items
     * @param array $items       The items
     * @param bool  $allowAdd    Indicate if the non-existent items must be added
     * @param bool  $resetSearch Reset item search
     *
     * @return array The choices with new items
     */
    public static function findItemsForTypes(array $choices, array $parents, array $items, $allowAdd, $resetSearch = false)
    {
        if ($allowAdd) {
            $prevItems = $parents;

            foreach ($items as $item) {
                $searchItems = $resetSearch ? $parents : $prevItems;
                $pos = array_search($item, $searchItems);

                if (false === $pos) {
                    $choices[static::getNewIndex($choices)] = $item;
                }
            }
        }

        return $choices;
    }

    /**
     * Get the new integer index.
     *
     * @param array $values The array
     *
     * @return int
     */
    protected static function getNewIndex(array $values)
    {
        $index = 0;
        $keys = array_keys($values);
        $size = count($keys);

        if ($size > 0) {
            $index = ((int) $keys[$size - 1]) + 1;
        }

        return $index;
    }
}
