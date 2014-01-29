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
use Symfony\Component\HttpFoundation\Request;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Select2EntityChoiceList;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class GetAjaxSelect2Event extends GetAjaxEvent
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param string  $id
     * @param Request $request
     * @param array   $options
     * @param string  $format
     */
    public function __construct($id, Request $request, array $options, $format = 'json')
    {
        parent::__construct($id, $format);

        $this->request = $request;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $ajaxSearch = $this->request->get('s', '');
        $ajaxPageNumber = $this->request->get('pn', 1);
        $ajaxPageSize = $this->request->get('ps', $this->options['ajaxPageSize']);
        $ajaxIds = $this->request->get('ids', '');

        if (in_array($ajaxIds, array(null, ''))) {
            $ajaxIds = array();

        } else {
            $ajaxIds = explode(',', $ajaxIds);
        }

        $choiceList = new Select2EntityChoiceList(
                        $this->options['em'],
                        $this->options['class'],
                        $this->options['property'],
                        $this->options['query_builder'],
                        $this->options['choices'],
                        $this->options['group_by'],
                        $this->options['ajax'],
                        $ajaxSearch,
                        $ajaxPageNumber,
                        $ajaxPageSize,
                        $this->options['multiple'] ? $ajaxIds : array()
                    );

        return array(
                'length'      => $choiceList->getLength(),
                'page_number' => $ajaxPageNumber,
                'page_size'   => $ajaxPageSize,
                'search'      => $ajaxSearch,
                'results'     => $choiceList->getChoices(),
        );
    }
}
