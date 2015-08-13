<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\Loader;

use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxEntityLoaderInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxDoctrineChoiceLoader extends DynamicDoctrineChoiceLoader implements AjaxChoiceLoaderInterface
{
    /**
     * @var AjaxEntityLoaderInterface
     */
    protected $objectLoader;

    /**
     * @var int
     */
    protected $pageSize;

    /**
     * @var int
     */
    protected $pageNumber;

    /**
     * @var string
     */
    protected $search;

    /**
     * @var array
     */
    protected $ids;

    /**
     * Creates a new choice loader.
     *
     * @param AjaxEntityLoaderInterface         $objectLoader The objects loader
     * @param IdReader                          $idReader     The reader for the object
     *                                                        IDs.
     * @param null|callable|string|PropertyPath $label        The callable or path generating the choice labels
     * @param ChoiceListFactoryInterface|null   $factory      The factory for creating
     *                                                        the loaded choice list
     */
    public function __construct(AjaxEntityLoaderInterface $objectLoader, IdReader $idReader, $label, $factory = null)
    {
        parent::__construct($objectLoader, $idReader, $label, $factory);

        $this->pageSize = 10;
        $this->pageNumber = 1;
        $this->search = '';
        $this->ids = array();
        $this->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->objectLoader->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function setPageSize($size)
    {
        $this->pageSize = $size;

        return $this;
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

        return $this;
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

        return $this;
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

        return $this;
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
    public function loadPaginatedChoiceList($value = null)
    {
        $objects = $this->objectLoader->getPaginatedEntities($this->getPageSize(), $this->getPageNumber());

        if (null === $value && $this->idReader->isSingleId()) {
            $value = array($this->idReader, 'getIdValue');
        }

        return $this->factory->createListFromChoices($objects, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function loadChoiceListForView(array $values, $value = null)
    {
        $choiceList = $this->factory->createListFromChoices($values, $value);

        return $choiceList;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->objectLoader->reset();
        $this->objectLoader->setSearch((string) $this->label, $this->getSearch());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function loadEntities()
    {
        return array();
    }
}
