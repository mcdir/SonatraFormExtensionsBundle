<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Util\StringUtil;

/**
 * Base of choice type extension for types that have choice type for parent type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class BaseChoiceSelect2TypeExtension extends AbstractTypeExtension
{
    /**
     * @var string
     */
    protected $type;

    /**
     * Constructor.
     *
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (null === $options['select2']['ajax_route']) {
            $builder->setAttribute('select2_ajax_route', 'sonatra_form_extensions_ajax_'.StringUtil::fqcnToBlockPrefix($this->type));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return $this->type;
    }
}
