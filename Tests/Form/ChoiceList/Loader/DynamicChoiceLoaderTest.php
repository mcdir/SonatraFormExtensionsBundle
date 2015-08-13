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

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\DynamicChoiceLoader;

/**
 * Tests case for dynamic choice loader.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DynamicChoiceLoaderTest extends AbstractChoiceLoaderTest
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

        return new DynamicChoiceLoader($choices, true);
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
                'Group 2' => array(
                    'Baz' => '2',
                ),
            );
        }

        return array(
            'Bar' => '0',
            'Foo' => '1',
            'Baz' => '2',
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
                'Test' => '3',
            );
        } else {
            $existing['Test'] = '3';
        }

        return $existing;
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

    /**
     * @dataProvider getIsGroup
     *
     * @param bool $group
     */
    public function testLoadFlippedChoiceListForView($group)
    {
        if ($group) {
            $choices = array(
                'Group 1' => array(
                    'foo' => 'Bar',
                    'bar' => 'Foo',
                ),
                'Group 2' => array(
                    'baz' => 'Baz',
                ),
            );
            $validValues = array(
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
                'foo' => 'Bar',
                'bar' => 'Foo',
                'baz' => 'Baz',
            );
            $validValues = array(
                'Bar' => 'foo',
                'Foo' => 'bar',
                'Baz' => 'baz',
            );
        }

        $loader = new DynamicChoiceLoader($choices, false);
        $choiceList = $loader->loadChoiceListForView(array('foo', 'bar', 'test'));

        $this->assertInstanceOf('Symfony\Component\Form\ChoiceList\ChoiceListInterface', $choiceList);
        $this->assertEquals($validValues, $choiceList->getStructuredValues());
    }
}
