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
use Sonatra\Bundle\FormExtensionsBundle\Form\DataTransformer\ChoiceToValueTransformer;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Fixtures\FixtureAjaxChoiceListFormatter;

/**
 * Tests case for choice to value transformer.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ChoiceToValueTransformerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChoiceToValueTransformer
     */
    protected $transformer;

    protected function setUp()
    {
        $list = new AjaxSimpleChoiceList(new FixtureAjaxChoiceListFormatter(), array('' => 'A', 0 => 'B', 1 => 'C'));
        $this->transformer = new ChoiceToValueTransformer($list);
    }

    protected function tearDown()
    {
        $this->transformer = null;
    }

    public function transformProvider()
    {
        return array(
            // more extensive test set can be found in FormUtilTest
            array(0, '0'),
            array(false, '0'),
            array('', ''),
        );
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform($in, $out)
    {
        $this->assertSame($out, $this->transformer->transform($in));
    }

    public function reverseTransformProvider()
    {
        return array(
            // values are expected to be valid choice keys already and stay
            // the same
            array('0', 0),
        );
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($in, $out)
    {
        $this->assertSame($out, $this->transformer->reverseTransform($in));
    }

    public function reverseInvalidTransformProvider()
    {
        return array(
            array('', null),
            array(null, null),
        );
    }

    /**
     * @dataProvider reverseInvalidTransformProvider
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testReverseTransformExpectsRequired($in)
    {
        $this->transformer->reverseTransform($in);
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testReverseTransformExpectsScalar()
    {
        $this->transformer->reverseTransform(array());
    }

    public function testReverseTransformNotRequired()
    {
        $list = new AjaxSimpleChoiceList(new FixtureAjaxChoiceListFormatter(), array('' => 'A', 0 => 'B', 1 => 'C'));
        $this->transformer = new ChoiceToValueTransformer($list, false);

        $this->assertNull($this->transformer->reverseTransform(''));
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\TransformationFailedException
     */
    public function testReverseTransformExpectsNotUnique()
    {
        $this->transformer->reverseTransform('-1');
    }
}
