<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Event;

use Sonatra\Bundle\AjaxBundle\Event\GetAjaxEvent;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\Helper\AjaxChoiceListHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GetAjaxChoiceListEvent extends GetAjaxEvent
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var AjaxChoiceListInterface
     */
    protected $choiceList;

    /**
     * Constructor.
     *
     * @param string                  $id
     * @param RequestStack            $requestStack
     * @param AjaxChoiceListInterface $choiceList
     * @param string                  $format
     */
    public function __construct($id, RequestStack $requestStack, AjaxChoiceListInterface $choiceList, $format = 'json')
    {
        parent::__construct($id, $format);

        $this->request = $requestStack->getMasterRequest();
        $this->choiceList = $choiceList;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return AjaxChoiceListHelper::getData($this->request, $this->choiceList, $this->getId().'_');
    }
}
