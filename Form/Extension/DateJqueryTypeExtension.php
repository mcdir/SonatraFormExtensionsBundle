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

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DateJqueryTypeExtension extends DateTimeJqueryTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'date_picker' => true,
            'time_picker' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'date';
    }
}
