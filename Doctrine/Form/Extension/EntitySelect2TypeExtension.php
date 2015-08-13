<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\Extension;

use Doctrine\Common\Persistence\ObjectManager;
use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxORMQueryBuilderLoader;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class EntitySelect2TypeExtension extends DoctrineSelect2TypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new AjaxORMQueryBuilderLoader(
            $queryBuilder,
            $manager,
            $class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilderPartsForCachingHash($queryBuilder)
    {
        return array(
            $queryBuilder->getQuery()->getSQL(),
            $queryBuilder->getParameters()->toArray(),
        );
    }
}
