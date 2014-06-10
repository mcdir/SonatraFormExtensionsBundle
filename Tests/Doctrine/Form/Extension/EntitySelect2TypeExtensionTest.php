<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Doctrine\Form\Extension;

use Doctrine\ORM\Tools\SchemaTool;
use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\Extension\EntitySelect2TypeExtension;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\Select2AjaxChoiceListFormatter;
use Sonatra\Bundle\FormExtensionsBundle\Form\Extension\ChoiceSelect2TypeExtension;
use Sonatra\Bundle\FormExtensionsBundle\Tests\Form\Extension\AbstractSelect2TypeExtensionTest;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Forms;

/**
 * Tests case for entity of select2 form extension type.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class EntitySelect2TypeExtensionTest extends AbstractSelect2TypeExtensionTest
{
    const SINGLE_INT_ID_CLASS = 'Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdEntity';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $emRegistry;

    /**
     * @var array
     */
    protected $items;

    protected function setUp()
    {
        parent::setUp();

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
        $this->emRegistry = $this->createRegistryMock('default', $this->em);
        $this->items = array();

        $schemaTool = new SchemaTool($this->em);
        $classes = array(
            $this->em->getClassMetadata(self::SINGLE_INT_ID_CLASS),
        );

        try {
            $schemaTool->dropSchema($classes);
        } catch (\Exception $e) {
        }

        try {
            $schemaTool->createSchema($classes);
        } catch (\Exception $e) {
        }

        $this->createEntities();

        /* @var ContainerInterface $container */
        $container = $this->container;

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(new ChoiceSelect2TypeExtension($container, $this->getExtensionTypeName(), 10))
            ->addType(new EntityType($this->emRegistry))
            ->addTypeExtension(new EntitySelect2TypeExtension())
            ->getFormFactory();

        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em = null;
        $this->items = null;
    }

    protected function createRegistryMock($name, $em)
    {
        $registry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())
            ->method('getManager')
            ->with($this->equalTo($name))
            ->will($this->returnValue($em))
        ;
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with($this->equalTo($this::SINGLE_INT_ID_CLASS))
            ->will($this->returnValue($em))
        ;

        return $registry;
    }

    protected function createEntities()
    {
        $item1 = new SingleIntIdEntity(1, 'Foo');
        $item2 = new SingleIntIdEntity(2, 'Bar');
        $item3 = new SingleIntIdEntity(3, 'Baz');
        $item4 = new SingleIntIdEntity(4, 'Boo!');

        $this->items = array(1 => $item1, 2 => $item2, 3 => $item3, 4 => $item4);

        $this->em->persist($item1);
        $this->em->persist($item2);
        $this->em->persist($item3);
        $this->em->persist($item4);
        $this->em->flush();
    }

    protected function mergeOptions(array $options)
    {
        $options = parent::mergeOptions($options);
        $options['class'] = $this::SINGLE_INT_ID_CLASS;

        return $options;
    }

    protected function getExtensionTypeName()
    {
        return 'entity';
    }

    protected function getSingleData()
    {
        return $this->items[1];
    }

    protected function getValidSingleValue()
    {
        return '1';
    }

    protected function getValidAjaxSingleValue()
    {
        return '1';
    }

    protected function getMultipleData()
    {
        return array($this->items[1], $this->items[2]);
    }

    protected function getValidMultipleValue()
    {
        return array('1', '2');
    }

    protected function getValidAjaxMultipleValue()
    {
        return implode(',', $this->getValidMultipleValue());
    }

    protected function getValidFirstChoiceSelected()
    {
        $formatter = new Select2AjaxChoiceListFormatter();
        $choice = new ChoiceView($this->items[1], '1', 'Foo');

        return $formatter->formatChoice($choice);
    }

    public function testWithQueryBuilder()
    {
        $qb = $this->em->createQueryBuilder()
            ->select('e')
            ->from($this::SINGLE_INT_ID_CLASS, 'e')
        ;
        $options = array(
            'query_builder' => $qb,
        );

        $form = $this->factory->create($this->getExtensionTypeName(), $this->getSingleData(), $this->mergeOptions($options));
        $config = $form->getConfig();

        $this->assertFalse($config->getOption('compound'));
        $this->assertFalse($config->getOption('multiple'));
        $this->assertTrue($config->hasOption('select2'));
        $this->assertTrue($config->getOption('select2')['enabled']);
        $this->assertFalse($config->getOption('select2')['ajax']);
        $this->assertFalse($config->getOption('select2')['allow_add']);
        $this->assertInstanceOf('Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface', $config->getOption('choice_list'));
    }
}
