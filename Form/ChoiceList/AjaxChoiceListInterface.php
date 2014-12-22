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

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AjaxChoiceListInterface extends ChoiceListInterface
{
    /**
     * Gets list of formatted choices for selected values (groups are not displayed).
     *
     * @param array $values
     *
     * @return array The list of formatted choices selected
     */
    public function getFormattedChoicesForValues(array $values);

    /**
     * Gets formatted choices.
     *
     * @return array The list or group list of formatted choices
     */
    public function getFormattedChoices();

    /**
     * Gets the first choice view of list or group choices.
     *
     * @return ChoiceView|null
     */
    public function getFirstChoiceView();

    /**
     * Set allow add.
     *
     * @param boolean $allowAdd
     *
     * @return self
     */
    public function setAllowAdd($allowAdd);

    /**
     * Check if allow add.
     *
     * @return boolean
     */
    public function getAllowAdd();

    /**
     * Get the size of all.
     *
     * @return integer
     */
    public function getSize();

    /**
     * Set page size.
     *
     * @param int $size
     *
     * @return self
     */
    public function setPageSize($size);

    /**
     * Get page size.
     *
     * @return int
     */
    public function getPageSize();

    /**
     * Set page number.
     *
     * @param int $number
     *
     * @return self
     */
    public function setPageNumber($number);

    /**
     * Get page number.
     *
     * @return int
     */
    public function getPageNumber();

    /**
     * Set search filter.
     *
     * @param string $search
     *
     * @return self
     */
    public function setSearch($search);

    /**
     * Get search filter.
     *
     * @return string
     */
    public function getSearch();

    /**
     * Set ids.
     *
     * @param array $ids
     *
     * @return self
     */
    public function setIds(array $ids);

    /**
     * Get ids.
     *
     * @return array
     */
    public function getIds();

    /**
     * Resets the choices with the filter conditions.
     *
     * @return self
     */
    public function reset();
}
