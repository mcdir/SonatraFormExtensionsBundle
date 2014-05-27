<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList;

use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AjaxChoiceListInterface extends ChoiceListInterface
{
    /**
     * Get the label of choices.
     *
     * @param array $values
     *
     * @return array The list of map 'id' and 'text'
     */
    public function getLabelChoicesForValues(array $values);

    /**
     * Get all data of choices.
     *
     * @return array The list of map 'id' and 'text'
     */
    public function getDataChoices();

    /**
     * Set allow add.
     *
     * @param boolean $allowAdd
     */
    public function setAllowAdd($allowAdd);

    /**
     * Check if allow add.
     *
     * @return boolean
    */
    public function getAllowAdd();

    /**
     * Set ajax.
     *
     * @param boolean $ajax
     */
    public function setAjax($ajax);

    /**
     * Get ajax.
     *
     * @return boolean
    */
    public function getAjax();

    /**
     * Indicates if the get values can be returned the data or empty array.
     *
     * @param bool $extractValues
     */
    public function setExtractValues($extractValues);

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
     */
    public function setIds(array $ids);

    /**
     * Get ids.
     *
     * @return array
     */
    public function getIds();
}
