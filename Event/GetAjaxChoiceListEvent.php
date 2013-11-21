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
use Symfony\Component\HttpFoundation\Request;

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
     * @param string  $id
     * @param Request $request
     * @param string  $format
     */
    public function __construct($id, Request $request, AjaxChoiceListInterface $choiceList, $format = 'json')
    {
        parent::__construct($id, $format);

        $this->request = $request;
        $this->choiceList = $choiceList;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $ajaxPageSize = $this->request->get('ps', $this->choiceList->getPageSize());
        $ajaxPageNumber = $this->request->get('pn', $this->choiceList->getPageNumber());
        $ajaxSearch = $this->request->get('s', '');
        $ajaxIds = $this->request->get('ids', '');

        if (in_array($ajaxIds, array(null, ''))) {
            $ajaxIds = array();

        } else {
            $ajaxIds = explode(',', $ajaxIds);
        }

        $this->choiceList->setPageSize($ajaxPageSize);
        $this->choiceList->setPageNumber($ajaxPageNumber);
        $this->choiceList->setSearch($ajaxSearch);
        $this->choiceList->setIds($ajaxIds);

        return array(
                'length'      => $this->choiceList->getSize(),
                'page_number' => $this->choiceList->getPageNumber(),
                'page_size'   => $this->choiceList->getPageSize(),
                'search'      => $this->choiceList->getSearch(),
                'results'     => $this->choiceList->getChoices(),
        );
    }
}
