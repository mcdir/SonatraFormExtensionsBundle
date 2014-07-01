<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\DataTransformer;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\ChoicesToValuesTransformer;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;

/**
 * Tests case for choices to values transformer.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoicesToValuesTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChoicesToValuesTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $list = new AjaxSimpleChoiceList(new FixtureAjaxChoiceListFormatter(), array(0 => 'A', 1 => 'B', 2 => 'C'));
        $this->transformer = new ChoicesToValuesTransformer($list, false);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function testTransform()
    {
        // Value strategy in SimpleChoiceList is to copy and convert to string
        $in = array(0, 1, 2);
        $out = array('0', '1', '2');

        $this->assertSame($out, $this->transformer->transform($in));
    }

    public function testTransformNull()
    {
        $this->assertSame(array(), $this->transformer->transform(null));
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testTransformExpectsArray()
    {
        $this->transformer->transform('foobar');
    }

    public function testReverseTransform()
    {
        // values are expected to be valid choices and stay the same
        $in = array('0', '1', '2');
        $out = array(0, 1, 2);

        $this->assertSame($out, $this->transformer->reverseTransform($in));
    }

    public function testReverseTransformNull()
    {
        $this->assertSame(array(), $this->transformer->reverseTransform(null));
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testReverseTransformExpectsArray()
    {
        $this->transformer->reverseTransform('foobar');
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testTransformNullExpectsNotFindAllMatchingChoices()
    {
        $this->assertSame(array(), $this->transformer->reverseTransform(array(0, 1, -1)));
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testTransformNullExpectsRequired()
    {
        $list = new AjaxSimpleChoiceList(new FixtureAjaxChoiceListFormatter(), array(0 => 'A', 1 => 'B', 2 => 'C'));
        $this->transformer = new ChoicesToValuesTransformer($list, true);

        $this->assertSame(array(), $this->transformer->reverseTransform(null));
    }
}
