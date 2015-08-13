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

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AjaxChoiceLoaderInterface extends DynamicChoiceLoaderInterface
{
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

    /**
     * Loads a paginated list of choices.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param null|callable $value The callable which generates the values
     *                             from choices
     *
     * @return ChoiceListInterface The loaded choice list
     */
    public function loadPaginatedChoiceList($value = null);
}
