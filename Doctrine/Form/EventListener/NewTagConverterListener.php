<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\EventListener;

use Sonatra\Bundle\FormExtensionsBundle\Doctrine\Form\Converter\NewTagConverterInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class NewTagConverterListener implements EventSubscriberInterface
{
    /**
     * @var NewTagConverterInterface
     */
    protected $converter;

    /**
     * Constructor.
     *
     * @param NewTagConverterInterface $converter The new tag converter
     */
    public function __construct(NewTagConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT => array('onSubmit', 70),
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (is_string($data)) {
            $event->setData($this->converter->convert($data));
        }
    }
}
