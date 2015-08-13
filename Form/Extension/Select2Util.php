<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\Extension;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoader;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\DynamicChoiceLoader;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\DynamicChoiceLoaderInterface;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\OptionsResolver\Options;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Select2Util
{
    /**
     * Convert the array to the ajax choice loader.
     *
     * @param ChoiceListFactoryInterface                                  $choiceListFactory The choice list factory
     * @param Options                                                     $options           The options
     * @param DynamicChoiceLoaderInterface|AjaxChoiceLoaderInterface|null $value             The value of choice loader normalizer
     *
     * @return DynamicChoiceLoaderInterface|AjaxChoiceLoaderInterface The dynamic choice loader
     */
    public static function convertToDynamicLoader(ChoiceListFactoryInterface $choiceListFactory, Options $options, $value)
    {
        if ($value instanceof DynamicChoiceLoaderInterface) {
            return $value;
        }

        if (!is_array($options['choices'])) {
            throw new InvalidConfigurationException('The "choice_loader" option must be an instance of DynamicChoiceLoaderInterface or the "choices" option must be an array');
        }

        if ($options['select2']['ajax']) {
            return new AjaxChoiceLoader($options['choices'],
                $options['choices_as_values'],
                $choiceListFactory);
        }

        return new DynamicChoiceLoader($options['choices'],
            $options['choices_as_values'],
            $choiceListFactory);
    }
}
