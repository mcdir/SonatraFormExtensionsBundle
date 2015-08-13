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

use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface DynamicChoiceLoaderInterface extends ChoiceLoaderInterface
{
    /**
     * Get the callable or path generating the choice labels.
     *
     * @return null|callable|string|PropertyPath
     */
    public function getLabel();

    /**
     * Get the size of all.
     *
     * @return int
     */
    public function getSize();

    /**
     * Set allow add.
     *
     * @param bool $allowAdd
     *
     * @return self
     */
    public function setAllowAdd($allowAdd);

    /**
     * Check if allow add.
     *
     * @return bool
     */
    public function isAllowAdd();

    /**
     * Load a choice list with only the selected choices dedicated for the view.
     *
     * @param array         $values The selected values
     * @param null|callable $value  The callable which generates the values
     *                              from choices
     *
     * @return ChoiceListInterface The loaded choice list with only selected choices
     */
    public function loadChoiceListForView(array $values, $value = null);
}
