<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\Type;

use Sonatra\Bundle\FormExtensionsBundle\Form\Type\AbstractSelect2Type;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxEntityChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxORMQueryBuilderLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class EntitySelect2Type extends AbstractSelect2Type
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Constructor.
     *
     * @param ContainerInterface        $container
     * @param ManagerRegistry           $registry
     * @param PropertyAccessorInterface $propertyAccessor
     * @param integer                   $defaultPageSize
     */
    public function __construct(ContainerInterface $container, ManagerRegistry $registry, PropertyAccessorInterface $propertyAccessor = null, $defaultPageSize = 10)
    {
        $this->registry = $registry;
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::getPropertyAccessor();

        parent::__construct($container, 'entity', $defaultPageSize);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $propertyAccessor = $this->propertyAccessor;
        $type = $this;

        $loader = function (Options $options) use ($type) {
            if (null !== $options['query_builder']) {
                return $type->getLoader($options['em'], $options['query_builder'], $options['class']);
            }

            $qb = $options['em']->createQueryBuilder()
                ->select('e')
                ->from($options['class'], 'e')
            ;

            return $type->getLoader($options['em'], $qb, $options['class']);
        };

        $choiceList = function (Options $options, $value) use ($propertyAccessor) {
            if (!$value instanceof AjaxChoiceListInterface) {
                $value = new AjaxEntityChoiceList(
                    $options['em'],
                    $options['class'],
                    $this->request,
                    $options['property'],
                    $options['loader'],
                    $options['choices'],
                    $options['preferred_choices'],
                    $options['group_by'],
                    $propertyAccessor
                );
            }

            return $value;
        };

        $resolver->setDefaults(array(
            'loader'      => $loader,
            'choice_list' => $choiceList,
        ));

        $resolver->setAllowedTypes(array(
            'loader' => 'Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxORMQueryBuilderLoader',
        ));

        parent::setDefaultOptions($resolver);
    }

    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param mixed         $queryBuilder
     * @param string        $class
     *
     * @return ORMQueryBuilderLoader
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        return new AjaxORMQueryBuilderLoader(
            $queryBuilder,
            $manager,
            $class
        );
    }
}
