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

use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Select2EntityChoiceList extends EntityChoiceList implements Select2ChoiceListInterface
{
    private $ajax;
    private $property;
    private $classMetadata;
    private $length;

    /**
     * Constructor.
     *
     * @param ObjectManager  $em
     * @param string         $class
     * @param string         $property
     * @param QueryBuilder   $qb
     * @param array|\Closure $choices
     * @param string         $groupBy
     * @param boolean        $ajax
     * @param string         $ajaxSearch
       @param integer        $ajaxPageNumber
       @param integer        $ajaxPageSize
       @param array          $ajaxIds
     */
    public function __construct(ObjectManager $em,
            $class,
            $property = null,
            QueryBuilder $qb = null,
            $choices = null,
            $groupBy = null,
            $ajax = false,
            $ajaxSearch = '',
            $ajaxPageNumber = 1,
            $ajaxPageSize = 10,
            array $ajaxIds = array())
    {
        $this->ajax = $ajax;
        $this->classMetadata = $em->getClassMetadata($class);

        if ($property) {
            $this->property = $property;
        }

        if (null === $qb) {
            $qb = new QueryBuilder($em);
            $qb->select('o')->from($class, 'o');
        }

        $entityAlias = $qb->getRootAlias();

        // hide selected value
        if (count($ajaxIds) > 0) {
            $qb->andWhere($qb->expr()->notIn("{$entityAlias}.id", $ajaxIds));
        }

        // search filter
        if (null !== $ajaxSearch && '' !== $ajaxSearch && $property) {
            $qb->andWhere($qb->expr()->like("{$entityAlias}.{$property}", ":{$property}" ));
            $qb->setParameter($property, "%{$ajaxSearch}%");
        }

        // order by
        $qb->orderBy($entityAlias.'.'.$property, 'ASC');

        if ($ajax) {
            // get length query
            $qbl = clone $qb;
            $qbl->setParameters($qb->getParameters());
            $qbl->select("count($entityAlias)");
            $this->length = (integer) $qbl->getQuery()->getSingleScalarResult();

            // ajax pagination
            $qb->setFirstResult(($ajaxPageNumber - 1) * $ajaxPageSize)
                ->setMaxResults($ajaxPageSize);
        }

        $loader = new ORMQueryBuilderLoader($qb, $em, $class);

        parent::__construct($em, $class, $property, $loader, $choices, array(), $groupBy);
    }

    /**
     * Get the length of all.
     *
     * @return integer
     */
    public function getLength()
    {
        if ($this->ajax) {
            return $this->length;

        } elseif (null === $this->length) {
            $this->length = count($this->getRemainingViews());
        }

        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices()
    {
        $choices = $this->getRemainingViews();
        $array = array();

        if (empty($choices)) {
            $choices = array();
        }

        foreach ($choices as $choice) {
            $array[] = array(
                'id'   => (string) $choice->value,
                'text' => $choice->label
            );
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferredViews()
    {
        if ($this->ajax) {
            return array();
        }

        return parent::getPreferredViews();
    }

    /**
     * Get intersaction $choices to $ids.
     *
     * @param array $ids
     *
     * @return array $intersect
     */
    public function getIntersect(array $ids)
    {
        $intersect = array();

        if ($this->ajax) {
            foreach ($this->getChoicesForValues($ids) as $entity) {
                $id = current($this->classMetadata->getIdentifierValues($entity));

                if ($this->property) {
                    $pa = PropertyAccess::getPropertyAccessor();
                    $label = $pa->getValue($entity, $this->property);

                } else {
                    $label = (string) $entity;
                }

                $intersect[] = array(
                    'id'   => (string) $id,
                    'text' => $label
                );
            }

        } else {
            foreach ($this->getChoices() as $choice) {
                if (in_array($choice['id'], $ids)) {
                    $intersect[] = $choice;
                }
            }
        }

        return $intersect;
    }

    /**
     * {@inheritdoc}
     */
    protected function load()
    {
        if (!$this->ajax) {
            parent::load();
        }
    }
}
