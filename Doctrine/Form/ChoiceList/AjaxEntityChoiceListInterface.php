<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\ChoiceList;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AjaxEntityChoiceListInterface extends AjaxChoiceListInterface
{
    /**
     * Defines if the choice list uses lazy loading.
     *
     * @param bool $value
     */
    public function setLazy($value);

    /**
     * Checks if the choice list uses the lazy loading.
     *
     * @return bool
     */
    public function isLazy();
}
