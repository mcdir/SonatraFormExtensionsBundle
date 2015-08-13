<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Loader;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoader;

/**
 * Tests case for ajax choice loader.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxChoiceLoaderTest extends AbstractAjaxChoiceLoaderTest
{
    /**
     * {@inheritdoc}
     */
    protected function createChoiceLoader($group = false)
    {
        if ($group) {
            $choices = array(
                'Group 1' => array(
                    'Bar' => 'foo',
                    'Foo' => 'bar',
                ),
                'Group 2' => array(
                    'Baz' => 'baz',
                ),
            );
        } else {
            $choices = array(
                'Bar' => 'foo',
                'Foo' => 'bar',
                'Baz' => 'baz',
            );
        }

        return new AjaxChoiceLoader($choices, true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidStructuredValues($group)
    {
        if ($group) {
            return array(
                'Group 1' => array(
                    'Bar' => '0',
                    'Foo' => '1',
                ),
            );
        }

        return array(
            'Bar' => '0',
            'Foo' => '1',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidStructuredValuesWithNewTags($group)
    {
        $existing = $this->getValidStructuredValues($group);

        if ($group) {
            $existing['-------'] = array(
                'Test' => '2',
            );
        } else {
            $existing['Test'] = '2';
        }

        return $existing;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidStructuredValuesForSearch($group)
    {
        if ($group) {
            $valid = array(
                'Group 1' => array(
                    'Bar' => '0',
                ),
                'Group 2' => array(
                    'Baz' => '1',
                ),
            );
        } else {
            $valid = array(
                'Bar' => '0',
                'Baz' => '1',
            );
        }

        return $valid;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidStructuredValuesForPagination($group, $pageNumber, $pageSize)
    {
        if ($group) {
            $valid = array(
                'Group 1' => array(
                    'Bar' => '0',
                    'Foo' => '1',
                ),
            );

            if ($pageSize <= 0) {
                $valid['Group 2'] = array(
                    'Baz' => '2',
                );
            }

            if ($pageNumber === 2) {
                $valid = array(
                    'Group 2' => array(
                        'Baz' => '0',
                    ),
                );
            }
        } else {
            $valid = array(
                'Bar' => '0',
                'Foo' => '1',
            );

            if ($pageSize <= 0) {
                $valid['Baz'] = '2';
            }

            if ($pageNumber === 2) {
                $valid = array(
                    'Baz' => '0',
                );
            }
        }

        return $valid;
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidChoicesForValues($group)
    {
        return array(
            'foo',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidChoicesForValuesWithNewTags($group)
    {
        return array(
            'foo',
            'Test',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataForValuesForChoices($group)
    {
        return array(
            'foo',
            'Test',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidValuesForChoices($group)
    {
        return array(
            '0',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataForValuesForChoicesWithNewTags($group)
    {
        return array(
            0,
            'Test',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getValidValuesForChoicesWithNewTags($group)
    {
        return array(
            '0',
            'Test',
        );
    }
}
