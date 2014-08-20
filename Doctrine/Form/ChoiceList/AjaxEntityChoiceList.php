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

use Doctrine\ORM\QueryBuilder;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Util;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxEntityChoiceList extends EntityChoiceList implements AjaxEntityChoiceListInterface
{
    /**
     * @var AjaxChoiceListFormatterInterface
     */
    private $formatter;

    /**
     * @var boolean
     */
    private $allowAdd;

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
     * @var int|QueryBuilder
     */
    private $size;

    /**
     * @var string
     */
    private $ajaxClass;

    /**
     * @var string
     */
    private $ajaxLabelPath;

    /**
     * @var PropertyAccessorInterface
     */
    private $ajaxPropertyAccessor;

    /**
     * @var AjaxORMQueryBuilderLoader
     */
    private $ajaxEntityLoader;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * @var bool
     */
    private $lazy;

    /**
     * @var QueryBuilder
     */
    private $qbForGetChoices;

    /**
     * @var array<string, array<int, object>>
     */
    private $lazyCache;

    /**
     * Creates a new ajax entity choice list.
     *
     * @param AjaxChoiceListFormatterInterface $formatter         The formatter
     * @param ObjectManager                    $manager           An EntityManager instance
     * @param string                           $class             The class name
     * @param string                           $labelPath         The property path used for the label
     * @param AjaxORMQueryBuilderLoader        $entityLoader      An optional query builder
     * @param array                            $entities          An array of choices
     * @param array                            $preferredEntities An array of preferred choices
     * @param string                           $groupPath         A property path pointing to the property used
     *                                                            to group the choices. Only allowed if
     *                                                            the choices are given as flat array.
     * @param PropertyAccessorInterface        $propertyAccessor  The reflection graph for reading property paths.
     */
    public function __construct(AjaxChoiceListFormatterInterface $formatter, ObjectManager $manager, $class, $labelPath = null, AjaxORMQueryBuilderLoader $entityLoader = null, $entities = null,  array $preferredEntities = array(), $groupPath = null, PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->formatter = $formatter;
        $this->allowAdd = false;
        $this->pageSize = 10;
        $this->pageNumber = 1;
        $this->search = '';
        $this->ids = array();
        $this->ajaxClass = $class;
        $this->ajaxLabelPath = $labelPath;
        $this->ajaxPropertyAccessor = null === $propertyAccessor ? PropertyAccess::createPropertyAccessor() : $propertyAccessor;
        $this->ajaxEntityLoader = $entityLoader;
        $this->manager = $manager;
        $this->lazy = false;
        $this->lazyCache = array();

        parent::__construct($manager, $class, $labelPath, $entityLoader, $entities,  $preferredEntities, $groupPath, $propertyAccessor);
    }

    /**
     * {@inheritdoc}
     */
    public function setLazy($value)
    {
        $this->lazy = (boolean) $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isLazy()
    {
        return $this->lazy;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoicesForValues(array $values)
    {
        if (empty($values) || '' === implode('', $values)) {
            return array();
        }

        $choices = parent::getChoicesForValues($values);

        if ($this->isLazy() && null !== $this->qbForGetChoices && count($values) !== count($choices)) {
            $identifier = current($this->manager->getClassMetadata($this->ajaxClass)->getIdentifierFieldNames());
            $findValues = $values;

            foreach ($choices as $choice) {
                $findValues[] = current($this->getIdentifierValues($choice));
            }

            $findValues = array_unique($findValues);
            $cacheId = implode(',', $findValues);

            if (!isset($this->lazyCache[$cacheId])) {
                $qb = clone $this->qbForGetChoices;
                $entityAliases = $qb->getRootAliases();
                $entityAlias = $entityAliases[0];
                $qb->andWhere($qb->expr()->in($entityAlias.'.'.$identifier, $findValues));

                $this->lazyCache[$cacheId] = $qb->getQuery()->getResult();
            }

            $choices = $this->lazyCache[$cacheId];
        }

        $idChoices = parent::getValuesForChoices($choices);

        return Util::findItemsForTypes($choices, $idChoices, $values, $this->getAllowAdd(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getValuesForChoices(array $values)
    {
        $parentValues = parent::getValuesForChoices($values);

        if ($this->getAllowAdd()) {
            $items = $parentValues;

            foreach ($values as $value) {
                if (!empty($value)) {
                    $value = $this->convertEntityToChoiceView($value)->value;
                    $pos = array_search($value, $items);

                    if (false === $pos) {
                        $parentValues[] = $value;
                    }
                }
            }
        }

        return $parentValues;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedChoicesForValues(array $values)
    {
        if (0 === count($values)) {
            return array();
        }

        $choices = array();
        $selectedChoices = $values;
        $keyValues = array_keys($values);

        if (!is_object($values[$keyValues[0]])) {
            $selectedChoices = $this->getChoicesForValues($values);
        }

        foreach ($selectedChoices as $choice) {
            $choiceView = $this->convertEntityToChoiceView($choice);
            $choices[] = $this->formatter->formatChoice($choiceView);
        }

        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormattedChoices()
    {
        $this->refreshRangePagination();
        $util = new EntityFormatterUtil($this, $this->formatter);

        return $util->getFormattedChoices();
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstChoiceView()
    {
        return Util::getFirstChoiceView($this->getChoiceViews());
    }

    /**
     * {@inheritdoc}
     */
    public function setAllowAdd($allowAdd)
    {
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
    public function getSize()
    {
        if ($this->size instanceof QueryBuilder && $this->getPageSize() > 0) {
            $qb = $this->size;
            $entityAliases = $qb->getRootAliases();
            $entityAlias = $entityAliases[0];

            $qb->setParameters($qb->getParameters());
            $qb->select("count($entityAlias)");
            $this->size = (integer) $qb->getQuery()->getSingleScalarResult();

        } elseif ($this->size instanceof QueryBuilder) {
            $choices = $this->getChoices();
            $this->size = count($choices);

        } elseif (null === $this->size) {
            $choices = $this->getChoices();
            $this->size = count($choices);
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function setPageSize($size)
    {
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
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->size = null;
        $this->lazyCache = array();
        $this->qbForGetChoices = null;

        $ref = new \ReflectionClass($this);
        $parent = $ref->getParentClass();
        $propLoaded = $parent->getProperty('loaded');
        $propLoaded->setAccessible(true);
        $propLoaded->setValue($this, false);

        if ($this->ajaxEntityLoader instanceof AjaxORMQueryBuilderLoader) {
            $this->ajaxEntityLoader->reset();

            $qb = $this->ajaxEntityLoader->getQueryBuilder();
            $entityAliases = $qb->getRootAliases();
            $entityAlias = $entityAliases[0];

            if (null !== $this->ajaxLabelPath) {
                // search filter
                if (null !== $this->getSearch() && '' !== $this->getSearch() && $this->ajaxLabelPath) {
                    $qb->andWhere($qb->expr()->like("{$entityAlias}.{$this->ajaxLabelPath}", ":{$this->ajaxLabelPath}" ));
                    $qb->setParameter($this->ajaxLabelPath, "%{$this->getSearch()}%");
                }
            }

            if ($this->isLazy()) {
                $this->qbForGetChoices = clone $qb;
            }

            // clone query builder for always get the real size
            if (null !== $this->getPageSize()) {
                $this->size = clone $qb;
            }

            // pagination
            if ($this->isLazy() && $this->getPageSize() > 0) {
                $qb->setFirstResult(($this->getPageNumber() - 1) * $this->getPageSize())
                    ->setMaxResults($this->getPageSize());
            }

        } elseif ($this->isLazy()) {
            throw new InvalidConfigurationException('The lazy loading of ajax entity choice list must have a "Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxORMQueryBuilderLoader"');
        }
    }

    /**
     * Gets all choice views, including preferred and remaining views.
     *
     * @return ChoiceView[]|array<string, ChoiceView[]>
     */
    protected function getChoiceViews()
    {
        return array_merge_recursive($this->getPreferredViews(), $this->getRemainingViews());
    }

    /**
     * Extracts the label of entity.
     *
     * @param object $entity
     *
     * @return string The label
     */
    protected function extractLabel($entity)
    {
        if ($this->ajaxLabelPath) {
            return $this->ajaxPropertyAccessor->getValue($entity, $this->ajaxLabelPath);
        }

        return (string) $entity;
    }

    /**
     * Converts the entity to choice view.
     *
     * @param object|string $entity
     *
     * @return ChoiceView
     */
    protected function convertEntityToChoiceView($entity)
    {
        $label = $entity;
        $value = $entity;

        if (is_object($entity)) {
            $label = $this->extractLabel($entity);
            $value = $this->manager->getClassMetadata($this->ajaxClass)->getIdentifierValues($entity);
            $value = $this->fixValues($value);
            $value = implode('', $value);
        }

        return new ChoiceView($entity, (string) $value, (string) $label);
    }

    /**
     * Returns the values of the identifier fields of an entity.
     *
     * @param object $entity The entity for which to get the identifier
     *
     * @return array The identifier values
     */
    protected function getIdentifierValues($entity)
    {
        $this->manager->initializeObject($entity);

        return $this->manager->getClassMetadata($this->ajaxClass)->getIdentifierValues($entity);
    }

    /**
     * Refresh the range pagination.
     *
     * @return void
     */
    protected function refreshRangePagination()
    {
        if (!$this->isLazy()
            && $this->ajaxEntityLoader instanceof AjaxORMQueryBuilderLoader
            && $this->getPageSize() > 0) {
            $qb = $this->ajaxEntityLoader->getQueryBuilder();

            $qb->setFirstResult(($this->getPageNumber() - 1) * $this->getPageSize())
                ->setMaxResults($this->getPageSize());
        }
    }
}
