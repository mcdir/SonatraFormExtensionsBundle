<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Formatter;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;

/**
 * Tests case for ajax choice list formatter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractAjaxChoiceListFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AjaxChoiceListFormatterInterface
     */
    protected $formatter;

    protected function setUp()
    {
        $this->formatter = $this->createFormatter();
    }

    protected function tearDown()
    {
        $this->formatter = null;
    }

    /**
     * @return AjaxChoiceListFormatterInterface
     */
    abstract protected function createFormatter();

    /**
     * @return array
     */
    abstract protected function getValidFormattedChoice();

    /**
     * @return array
     */
    abstract protected function getValidFormattedGroupChoice();

    /**
     * @return array
     */
    abstract protected function getValidGroupWithChoiceAdded();

    public function testFormatChoice()
    {
        $choice = new ChoiceView('foo', 'foo', 'Bar');
        $this->assertSame($this->getValidFormattedChoice(), $this->formatter->formatChoice($choice));
    }

    public function testFormatGroupChoice()
    {
        $this->assertSame($this->getValidFormattedGroupChoice(), $this->formatter->formatGroupChoice('Baz'));
    }

    public function testAddingChoiceInGroup()
    {
        $choice = new ChoiceView('foo', 'foo', 'Bar');
        $group = $this->formatter->formatGroupChoice('Baz');

        $this->assertSame($this->getValidGroupWithChoiceAdded(), $this->formatter->addChoiceInGroup($group, $choice));
    }

    public function testCheckingEmptyGroup()
    {
        $group = $this->formatter->formatGroupChoice('Baz');

        $this->assertTrue($this->formatter->isEmptyGroup($group));
    }

    public function testCheckingNotEmptyGroup()
    {
        $choice = new ChoiceView('foo', 'foo', 'Bar');
        $group = $this->formatter->formatGroupChoice('Baz');
        $group = $this->formatter->addChoiceInGroup($group, $choice);

        $this->assertFalse($this->formatter->isEmptyGroup($group));
    }
}
