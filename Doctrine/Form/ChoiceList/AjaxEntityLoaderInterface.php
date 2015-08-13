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

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AjaxEntityLoaderInterface extends EntityLoaderInterface
{
    /**
     * Set the search.
     *
     * @param string $identifier The field identifier for search
     * @param string $search     The search value
     */
    public function setSearch($identifier, $search);

    /**
     * Get the size.
     *
     * @return int
     */
    public function getSize();

    /**
     * Get the paginated entities.
     *
     * @param int $pageSize   The page size
     * @param int $pageNumber The page number
     *
     * @return object[]
     */
    public function getPaginatedEntities($pageSize, $pageNumber = 1);

    /**
     * Restores the query builder.
     */
    public function reset();
}
