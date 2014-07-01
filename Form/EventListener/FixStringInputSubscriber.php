<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Fix the string input for choices and collection.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FixStringInputSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => array('preSubmit', 10),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (is_array($data) && 1 === count($data) && is_string($data[0])) {
            $data = $data[0];
        }

        if (is_string($data)) {
            if ('' === $data) {
                $event->setData(array());

            } else {
                $event->setData(explode(',', $data));
            }
        }
    }
}
