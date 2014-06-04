<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Form\Exception\StringCastException;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxEntityChoiceList extends EntityChoiceList implements AjaxChoiceListInterface
{
    /**
     * @var boolean
     */
    private $allowAdd;

    /**
     * @var boolean
     */
    private $ajax;

    /**
     * @var bool
     */
    private $extractValues;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * @var int
     */
    private $pageNumber;

    /**
     * @var string
     */
    private $search;

    /**
     * @var array
     */
    private $ids;

    /**
     * @var array
     */
    private $cacheChoices;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $labelPath;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var AjaxORMQueryBuilderLoader
     */
    private $entityLoader;

    /**
     * @var boolean
     */
    private $filtered;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * Creates a new ajax entity choice list.
     *
     * @param ObjectManager             $manager           An EntityManager instance
     * @param string                    $class             The class name
     * @param string                    $labelPath         The property path used for the label
     * @param AjaxORMQueryBuilderLoader $entityLoader      An optional query builder
     * @param array                     $entities          An array of choices
     * @param array                     $preferredEntities An array of preferred choices
     * @param string                    $groupPath         A property path pointing to the property used
     *                                                     to group the choices. Only allowed if
     *                                                     the choices are given as flat array.
     * @param PropertyAccessorInterface $propertyAccessor  The reflection graph for reading property paths.
     */
    public function __construct(ObjectManager $manager, $class, $labelPath = null, AjaxORMQueryBuilderLoader $entityLoader = null, $entities = null,  array $preferredEntities = array(), $groupPath = null, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->allowAdd = false;
        $this->ajax = false;
        $this->extractValues = false;
        $this->pageSize = 10;
        $this->pageNumber = 1;
        $this->search = '';
        $this->ids = array();
        $this->class = $class;
        $this->labelPath = $labelPath;
        $this->propertyAccessor = $propertyAccessor;
        $this->entityLoader = $entityLoader;
        $this->filtered = false;
        $this->manager = $manager;

        parent::__construct($manager, $class, $labelPath, $entityLoader, $entities,  $preferredEntities, $groupPath, $propertyAccessor);
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices()
    {
        if (!$this->ajax || null === $this->entityLoader) {
            return parent::getChoices();
        }

        if (null !== $this->cacheChoices) {
            return $this->cacheChoices;
        }

        // get choices
        $choices = $this->getChoiceViews();

        $this->cacheChoices = array();

        foreach ($choices as $choice) {
            $this->cacheChoices[] = array(
                'id'   => (string) $choice->value,
                'text' => $choice->label
            );
        }

        return $this->cacheChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function getValues()
    {
        if ($this->ajax && !$this->extractValues) {
            return array();
        }

        $this->filterQuery();

        return parent::getValues();
    }

    /**
     * {@inheritdoc}
     */
    public function getPreferredViews()
    {
        if ($this->ajax && !$this->extractValues) {
            return array();
        }

        $this->filterQuery();

        return parent::getPreferredViews();
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingViews()
    {
        if ($this->ajax && !$this->extractValues) {
            return array();
        }

        $this->filterQuery();

        $choices = parent::getRemainingViews();

        if (!$this->ajax) {
            $this->size = count($choices);

            // groupable
            if ($this->size > 0 && is_string(array_keys($choices)[0])) {
                $this->size = 0;

                foreach ($choices as $subChoices) {
                    $this->size += count($subChoices);
                }
            }
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoicesForValues(array $values)
    {
        if (!$this->allowAdd || empty($values) || (1 === count($values) && '' === $values[0])) {
            return parent::getChoicesForValues($values);
        }

        if (empty($values) || (count($values) > 0 && '' === $values[0])) {
            return array();
        }

        $ref = new \ReflectionClass($this);
        $prop = $ref->getParentClass()->getProperty('idAsValue');
        $prop->setAccessible(true);
        $idAsValue = $prop->getValue($this);

        $prop = $ref->getParentClass()->getProperty('idField');
        $prop->setAccessible(true);
        $idField = $prop->getValue($this);

        $getIdentifierValues = $ref->getParentClass()->getMethod('getIdentifierValues');
        $getIdentifierValues->setAccessible(true);

        if ($idAsValue && $this->entityLoader) {
            $unorderedEntities = $this->entityLoader->getEntitiesByIds($idField, $values);
            $entitiesByValue = array();
            $entities = array();

            // Maintain order and indices from the given $values
            // An alternative approach to the following loop is to add the
            // "INDEX BY" clause to the Doctrine query in the loader,
            // but I'm not sure whether that's doable in a generic fashion.
            foreach ($unorderedEntities as $entity) {
                $value = $this->fixValue(current($getIdentifierValues->invokeArgs($this, array($entity))));
                $entitiesByValue[$value] = $entity;
            }

            foreach ($values as $i => $value) {
                if (isset($entitiesByValue[$value])) {
                    $entities[$i] = $entitiesByValue[$value];

                } else {
                    $entity = new $this->class();
                    $entity->{'set'.ucfirst($this->labelPath)}($value);

                    $this->manager->persist($entity);
                    $entities[$i] = $entity;
                }
            }

            return $entities;
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataChoices()
    {
        if ($this->ajax) {
            return array();
        }

        $choices = $this->getChoiceViews();

        $data = array();

        foreach ($choices as $choice) {
            $data[] = array(
                'id'   => (string) $choice->value,
                'text' => $choice->label
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesForChoices(array $values)
    {
        if (!$this->ajax) {
            return parent::getValuesForChoices($values);
        }

        $ref = new \ReflectionClass($this);
        $prop = $ref->getParentClass()->getProperty('idAsValue');
        $prop->setAccessible(true);
        $idAsValue = $prop->getValue($this);
        $getIdentifierValues = $ref->getParentClass()->getMethod('getIdentifierValues');
        $getIdentifierValues->setAccessible(true);

        if ($idAsValue) {
            $entities = $values;
            $values = array();

            foreach ($entities as $i => $entity) {
                if ($entity instanceof $this->class) {
                    // Make sure to convert to the right format
                    $values[$i] = $this->fixValue(current($getIdentifierValues->invokeArgs($this, array($entity))));
                }
            }

            return $values;
        }

        return $values;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabelChoicesForValues(array $values)
    {
        if (0 === count($values)) {
            return array();
        }

        $choices = array();
        $ids = array();
        $labels = array();

        foreach ($values as $value) {
            if ($value instanceof ChoiceView) {
                $choices[] = $choices[] = array(
                    'id'   => (string) $value->value,
                    'text' => $value->label
                );

            } elseif (is_object($value)) {
                $choices[] = $value;

            } else {
                $ids[] = $value;
            }
        }

        if (count($ids) > 0) {
            $class = $this->entityLoader->getQueryBuilder()->getRootEntities()[0];

            $qb = $this->entityLoader->getQueryBuilder()->getEntityManager()->createQueryBuilder();
            $qb
                ->select('e')
                ->from($class, 'e')
                ->where($qb->expr()->in('e.id', $ids));
            ;

            $choices = array_merge($choices, $qb->getQuery()->execute());
        }

        $this->extractLabels($choices, $labels);

        return $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowAdd($allowAdd)
    {
        $this->reset();
        $this->allowAdd = $allowAdd;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowAdd()
    {
        return $this->allowAdd;
    }

    /**
     * {@inheritdoc}
     */
    public function setAjax($ajax)
    {
        $this->reset();
        $this->ajax = $ajax;
    }

    /**
     * {@inheritdoc}
     */
    public function getAjax()
    {
        return $this->ajax;
    }

    /**
     * {@inheritdoc}
     */
    public function setExtractValues($extractValues)
    {
        $this->extractValues = (bool) $extractValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if (null === $this->size) {
            $this->getChoiceViews();
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageSize($size)
    {
        $this->reset();
        $this->pageSize = $size;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageNumber($number)
    {
        $this->reset();
        $this->pageNumber = $number;
    }

    /**
     * {@inheritdoc}
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearch($search)
    {
        $this->reset();
        $this->search = $search;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearch()
    {
        return $this->search;
    }

    /**
     * {@inheritdoc}
     */
    public function setIds(array $ids)
    {
        $this->reset();
        $this->ids = $ids;
    }

    /**
     * {@inheritdoc}
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * Reset the cache.
     */
    protected function reset()
    {
        $this->cacheChoices = null;
        $this->size = null;
        $this->filtered = false;

        if ($this->entityLoader instanceof AjaxORMQueryBuilderLoader) {
            $this->entityLoader->reset();
        }

        $ref = new \ReflectionClass($this);
        $parent = $ref->getParentClass();
        $prop = $parent->getProperty('loaded');
        $prop->setAccessible(true);
        $prop->setValue($this, false);
    }

    /**
     * Filter query.
     *
     * @return array
     */
    protected function filterQuery()
    {
        if ($this->ajax && !$this->filtered && $this->entityLoader instanceof AjaxORMQueryBuilderLoader) {
            $qb = $this->entityLoader->getQueryBuilder();

            $entityAlias = $qb->getRootAliases()[0];

            // hide selected value
            if (count($this->getIds()) > 0) {
                $qb->andWhere($qb->expr()->notIn("{$entityAlias}.id", $this->getIds()));
            }

            if (null !== $this->labelPath) {
                // search filter
                if (null !== $this->getSearch() && '' !== $this->getSearch() && $this->labelPath) {
                    $qb->andWhere($qb->expr()->like("{$entityAlias}.{$this->labelPath}", ":{$this->labelPath}" ));
                    $qb->setParameter($this->labelPath, "%{$this->getSearch()}%");
                }

                // order by
                $qb->orderBy($entityAlias.'.'.$this->labelPath, 'ASC');
            }

            // get size
            $qbl = clone $qb;
            $qbl->setParameters($qb->getParameters());
            $qbl->select("count($entityAlias)");
            $this->size = (integer) $qbl->getQuery()->getSingleScalarResult();

            // adds the selected entities
            if (count($this->getIds()) > 0) {
                $qb->orWhere($qb->expr()->in("{$entityAlias}.id", $this->getIds()));
            }

            // pagination
            $qb->setFirstResult(($this->getPageNumber() - 1) * $this->getPageSize())
                ->setMaxResults($this->getPageSize());

            $this->filtered = true;
        }
    }

    /**
     * Exctract entity labels.
     *
     * @param array|\Iterator $choices
     * @param array           $labels
     *
     * @throws StringCastException
     */
    protected function extractLabels($choices, array &$labels)
    {
        foreach ($choices as $i => $choice) {
            if (is_array($choice)) {
                $labels[$i] = array();
                $this->extractLabels($choice, $labels[$i]);

            } elseif ($this->labelPath) {
                $labels[$i] = array(
                    'id'   => $this->propertyAccessor->getValue($choice, 'id'),
                    'text' => $this->propertyAccessor->getValue($choice, $this->labelPath),
                );

            } elseif (method_exists($choice, '__toString')) {
                $labels[$i] = (string) $choice;

            } else {
                throw new StringCastException(sprintf('A "__toString()" method was not found on the objects of type "%s" passed to the choice field. To read a custom getter instead, set the argument $labelPath to the desired property path.', get_class($choice)));
            }
        }
    }

    /**
     * Gets all choice views, including preferred and remaining views.
     *
     * @return array<int, ChoiceView>|array<string, ChoiceView[]>
     */
    protected function getChoiceViews()
    {
        return array_merge_recursive($this->getPreferredViews(), $this->getRemainingViews());
    }
}
