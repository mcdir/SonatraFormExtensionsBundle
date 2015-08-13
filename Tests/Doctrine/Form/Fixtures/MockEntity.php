<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Doctrine\Form\Fixtures;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockEntity
{
    /**
     * @var int|string
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @param string|null $id
     * @param string|null $label
     */
    public function __construct($id = null, $label = null)
    {
        $this->id = $id;
        $this->label = $label;
    }

    /**
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }
}
