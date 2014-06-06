<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Doctrine\Form\ChoiceList;

use Doctrine\ORM\Tools\SchemaTool;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\AbstractAjaxChoiceListTest;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;

/**
 * Abstract configuration tests for extend AJAX entity choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class AbstractExtendAjaxEntityChoiceListTest extends AbstractAjaxChoiceListTest
{
    const SINGLE_INT_ID_CLASS = 'Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdEntity';

    const GROUPABLE_CLASS = 'Symfony\Bridge\Doctrine\Tests\Fixtures\GroupableEntity';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $items;

    protected function setUp()
    {
        if (!class_exists('Symfony\Component\Form\Form')) {
            $this->markTestSkipped('The "Form" component is not available');
        }

        if (!class_exists('Doctrine\DBAL\Platforms\MySqlPlatform')) {
            $this->markTestSkipped('Doctrine DBAL is not available.');
        }

        if (!class_exists('Doctrine\Common\Version')) {
            $this->markTestSkipped('Doctrine Common is not available.');
        }

        if (!class_exists('Doctrine\ORM\EntityManager')) {
            $this->markTestSkipped('Doctrine ORM is not available.');
        }

        $this->em = DoctrineTestHelper::createTestEntityManager();
        $this->items = array();

        $schemaTool = new SchemaTool($this->em);
        $classes = array(
            $this->em->getClassMetadata(self::SINGLE_INT_ID_CLASS),
            $this->em->getClassMetadata(self::GROUPABLE_CLASS),
        );

        try {
            $schemaTool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (\Exception $e) {
        }

        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em = null;
        $this->items = null;
    }
}
