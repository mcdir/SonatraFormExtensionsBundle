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

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Resize a string collection form element based on the data sent from the client.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class StringResizeFormListener implements EventSubscriberInterface
{
    /**
     * @var boolean
     */
    private $required;

    /**
     * Constructor.
     *
     * @param boolean $required
     */
    public function __construct($required = true)
    {
        $this->required = $required;
    }

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
        $event->setData(null);

        if (null === $data || '' === $data) {
            if ($this->required) {
                $event->setData('');
            }

            return;
        }

        $event->setData(explode(',', $data));
    }
}
