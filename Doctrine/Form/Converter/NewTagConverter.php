<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\Converter;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class NewTagConverter implements NewTagConverterInterface
{
    /**
     * @var
     */
    protected $class;

    /**
     * @var
     */
    protected $label;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    /**
     * Constructor.
     *
     * @param string $class The class name
     * @param string $label The label
     */
    public function __construct($class, $label)
    {
        $this->class = $class;
        $this->label = $label;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function convert($value)
    {
        $ref = new \ReflectionClass($this->class);
        $data = $ref->newInstanceWithoutConstructor();
        $this->propertyAccessor->setValue($data, $this->label, $value);

        return (object) $data;
    }
}
