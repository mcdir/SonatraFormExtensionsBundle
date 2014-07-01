<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Doctrine\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList\AjaxORMQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Tests\DoctrineOrmTestCase;
use Doctrine\DBAL\Connection;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxORMQueryBuilderLoaderTest extends DoctrineOrmTestCase
{
    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testItOnlyWorksWithQueryBuilderOrClosure()
    {
        new AjaxORMQueryBuilderLoader(new \stdClass());
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     */
    public function testClosureRequiresTheEntityManager()
    {
        $closure = function () {};

        new AjaxORMQueryBuilderLoader($closure);
    }

    public function testIdentifierTypeIsStringArray()
    {
        $this->checkIdentifierType('Symfony\Bridge\Doctrine\Tests\Fixtures\SingleStringIdEntity', Connection::PARAM_STR_ARRAY);
    }

    public function testIdentifierTypeIsIntegerArray()
    {
        $this->checkIdentifierType('Symfony\Bridge\Doctrine\Tests\Fixtures\SingleIntIdEntity', Connection::PARAM_INT_ARRAY);
    }

    protected function checkIdentifierType($classname, $expectedType)
    {
        $em = $this->createTestEntityManager();

        $query = $this->getMockBuilder('QueryMock')
            ->setMethods(array('setParameter', 'getResult', 'getSql', '_doExecute'))
            ->getMock();

        $query->expects($this->once())
            ->method('setParameter')
            ->with('ORMQueryBuilderLoader_getEntitiesByIds_id', array(), $expectedType)
            ->will($this->returnValue($query));

        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setConstructorArgs(array($em))
            ->setMethods(array('getQuery'))
            ->getMock();

        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        /* @var \Doctrine\ORM\QueryBuilder $qb */
        $qb->select('e')
            ->from($classname, 'e');

        $loader = new AjaxORMQueryBuilderLoader($qb);
        $loader->getEntitiesByIds('id', array());
        $this->assertEquals($qb, $loader->getQueryBuilder());
        $loader->reset();
        $this->assertEquals($qb, $loader->getQueryBuilder());
    }
}
