<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class LoaderUtil
{
    /**
     * Paginate the flatten choices.
     *
     * @param AjaxChoiceLoaderInterface $choiceLoader The choice loader
     * @param array                     $choices      The choices
     *
     * @return array The paginated choices
     */
    public static function paginateChoices(AjaxChoiceLoaderInterface $choiceLoader, array $choices)
    {
        list($startTo, $endTo) = static::getRangeValues($choiceLoader);

        // group
        if (is_array(current($choices))) {
            return static::paginateGroupChoices($choices, $startTo, $endTo);
        }

        return static::paginateSimpleChoices($choices, $startTo, $endTo);
    }

    /**
     * Gets range values.
     *
     * @param AjaxChoiceLoaderInterface $choiceLoader The ajax choice loader
     *
     * @return int[] The startTo and endTo
     */
    protected static function getRangeValues(AjaxChoiceLoaderInterface $choiceLoader)
    {
        $startTo = ($choiceLoader->getPageNumber() - 1) * $choiceLoader->getPageSize();
        $startTo = $startTo < 0 ? 0 : $startTo;
        $endTo = $startTo + $choiceLoader->getPageSize();

        if (0 >= $choiceLoader->getPageSize()) {
            $endTo = $choiceLoader->getSize();
        }

        if ($endTo > $choiceLoader->getSize()) {
            $endTo = $choiceLoader->getSize();
        }

        return array($startTo, $endTo);
    }

    /**
     * Paginate the flatten simple choices.
     *
     * @param array $choices The choices
     * @param int   $startTo The start index of pagination
     * @param int   $endTo   The end index of pagination
     *
     * @return array The paginated choices
     */
    protected static function paginateSimpleChoices(array $choices, $startTo, $endTo)
    {
        $paginatedChoices = array();
        $index = 0;

        foreach ($choices as $key => $choice) {
            if ($index >= $startTo && $index < $endTo) {
                $paginatedChoices[$key] = $choice;
            }

            ++$index;
        }

        return $paginatedChoices;
    }

    /**
     * Paginate the flatten group choices.
     *
     * @param array $choices The choices
     * @param int   $startTo The start index of pagination
     * @param int   $endTo   The end index of pagination
     *
     * @return array The paginated choices
     */
    protected static function paginateGroupChoices(array $choices, $startTo, $endTo)
    {
        $paginatedChoices = array();
        $index = 0;

        foreach ($choices as $groupName => $groupChoices) {
            foreach ($groupChoices as $key => $choice) {
                if ($index >= $startTo && $index < $endTo) {
                    $paginatedChoices[$groupName][$key] = $choice;
                }

                ++$index;
            }
        }

        return $paginatedChoices;
    }
}
