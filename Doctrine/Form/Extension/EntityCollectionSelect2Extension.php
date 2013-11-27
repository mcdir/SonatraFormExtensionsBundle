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

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Entity Collection Form Extension.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class EntityCollectionSelect2Extension extends AbstractTypeExtension
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
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if ('entity_select2' !== $options['type']) {
            return;
        }

        // Convert entity to string id
        if (!empty($view->vars['value'])) {
            $class = get_class($view->vars['value'][0]);
            $manager = $this->registry->getManagerForClass($class);
            $classMetadata = $manager->getClassMetadata($class);
            $identifier = $classMetadata->getIdentifierFieldNames();
            $idField = null;

            if (1 === count($identifier)) {
                $idField = $identifier[0];
            }

            foreach ($view->vars['value'] as $index => $entity) {
                $view->vars['value'][$index] = (string) $this->propertyAccessor->getValue($entity, $idField);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $ref = new \ReflectionClass($resolver);
        $prop = $ref->getProperty('defaultOptions');
        $prop->setAccessible(true);
        $old = $prop->getValue($resolver);
        $ref = new \ReflectionClass($old);
        $prop = $ref->getProperty('normalizers');
        $prop->setAccessible(true);
        $parentNormaliserType = $prop->getValue($old)['type'];

        $resolver->setNormalizers(array(
            'type' => function (Options $options, $value) use ($parentNormaliserType) {
                $value = $parentNormaliserType($options, $value);

                if ('entity' === $value) {
                    return 'entity_select2';
                }

                return $value;
            },
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'collection_select2';
    }
}
