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

use Doctrine\ORM\QueryBuilder;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Component\PropertyAccess\PropertyAccess;
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
     * @var ObjectManager
     */
    private $manager;

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
        $this->class = $class;
        $this->labelPath = $labelPath;
        $this->propertyAccessor = null === $propertyAccessor ? PropertyAccess::createPropertyAccessor() : $propertyAccessor;
        $this->entityLoader = $entityLoader;
        $this->manager = $manager;

        parent::__construct($manager, $class, $labelPath, $entityLoader, $entities,  $preferredEntities, $groupPath, $propertyAccessor);
    }

    /**
     * {@inheritdoc}
     */
    public function getChoicesForValues(array $values)
    {
        $choices = parent::getChoicesForValues($values);
        $idChoices = parent::getValuesForChoices($choices);

        if ($this->getAllowAdd()) {
            foreach ($values as $value) {
                $pos = array_search($value, $idChoices);

                if (false === $pos) {
                    $choices[] = $value;
                }
            }
        }

        return $choices;
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

        if (!is_object($values[array_keys($values)[0]])) {
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
        if ($this->entityLoader instanceof AjaxORMQueryBuilderLoader) {
            $qb = $this->entityLoader->getQueryBuilder();

            $qb->setFirstResult(($this->getPageNumber() - 1) * $this->getPageSize())
                ->setMaxResults($this->getPageSize());
        }

        $choices = $this->getChoiceViews();
        $formattedChoices = array();

        // simple
        if (count($choices) > 0 && is_int(array_keys($choices)[0])) {
            foreach ($choices as $choice) {
                $formattedChoices[] = $this->formatter->formatChoice($choice);
            }

            return $formattedChoices;
        }

        // group
        foreach ($choices as $groupName => $groupChoices) {
            $group = $this->formatter->formatGroupChoice($groupName);

            foreach ($groupChoices as $subChoice) {
                $group = $this->formatter->addChoiceInGroup($group, $subChoice);
            }

            if (!$this->formatter->isEmptyGroup($group)) {
                $formattedChoices[] = $group;
            }
        }

        return $formattedChoices;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstChoiceView()
    {
        $choices = $this->getChoiceViews();
        $firstChoice = null;

        if (count($choices) > 0) {
            $firstChoice = $choices[array_keys($choices)[0]];

            // group
            if (is_array($firstChoice) && count($firstChoice) > 0) {
                $firstChoice = $firstChoice[array_keys($firstChoice)[0]];
            }
        }

        return $firstChoice;
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
        if (null === $this->size) {
            $choices = $this->getChoices();
            $this->size = count($choices);

            // group
            if ($this->size > 0 && is_array($choices[array_keys($choices)[0]])) {
                $this->size = 0;

                foreach ($choices as $subChoices) {
                    $this->size += count($subChoices);
                }
            }

        } elseif ($this->size instanceof QueryBuilder) {
            $qb = $this->size;
            $entityAlias = $qb->getRootAliases()[0];

            $qb->setParameters($qb->getParameters());
            $qb->select("count($entityAlias)");
            $this->size = (integer) $qb->getQuery()->getSingleScalarResult();
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

        $ref = new \ReflectionClass($this);
        $parent = $ref->getParentClass();
        $propLoaded = $parent->getProperty('loaded');
        $propLoaded->setAccessible(true);
        $propLoaded->setValue($this, false);

        if ($this->entityLoader instanceof AjaxORMQueryBuilderLoader) {
            $this->entityLoader->reset();

            $qb = $this->entityLoader->getQueryBuilder();

            $entityAlias = $qb->getRootAliases()[0];

            if (null !== $this->labelPath) {
                // search filter
                if (null !== $this->getSearch() && '' !== $this->getSearch() && $this->labelPath) {
                    $qb->andWhere($qb->expr()->like("{$entityAlias}.{$this->labelPath}", ":{$this->labelPath}" ));
                    $qb->setParameter($this->labelPath, "%{$this->getSearch()}%");
                }
            }

            // clone query builder for always get the real size
            if (null !== $this->getPageSize()) {
                $this->size = clone $qb;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize($choices, array $labels, array $preferredChoices)
    {
        parent::initialize($choices, $labels, $preferredChoices);
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
     *
     * @throws StringCastException When the "__toString()" method was not found on the object
     */
    protected function extractLabel($entity)
    {
        if ($this->labelPath) {
            return $this->propertyAccessor->getValue($entity, $this->labelPath);

        } elseif (method_exists($entity, '__toString')) {
            return (string) $entity;
        }

        throw new StringCastException(sprintf('A "__toString()" method was not found on the objects of type "%s" passed to the choice field. To read a custom getter instead, set the argument $labelPath to the desired property path.', get_class($entity)));
    }

    /**
     * Converts the entity to choice view.
     *
     * @param object $entity
     *
     * @return ChoiceView
     */
    protected function convertEntityToChoiceView($entity)
    {
        $label = $entity;
        $value = $entity;

        if (is_object($entity)) {
            $label = $this->extractLabel($entity);
            $value = $this->manager->getClassMetadata($this->class)->getIdentifierValues($entity);
            $value = $this->fixValues($value);
            $value = implode('', $value);
        }

        return new ChoiceView($entity, $value, $label);
    }
}
