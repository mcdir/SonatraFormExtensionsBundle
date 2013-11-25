<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\DataMapper;

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Lets have a compound block with a data mapper not doing any work by default.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CollectionMapper implements DataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($data, $forms)
    {
        $empty = null === $data || array() === $data;

        if (!$empty && !is_array($data) && !is_string($data)) {
            throw new UnexpectedTypeException($data, 'string, array or empty');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$data)
    {
        $data = array();

        foreach ($forms as $form) {
            $data[] = $form->getData();
        }
    }
}
