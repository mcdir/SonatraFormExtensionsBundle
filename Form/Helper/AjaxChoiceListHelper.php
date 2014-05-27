<?php

/**
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\Helper;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxChoiceListInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\AjaxSimpleChoiceList;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Helper for generate the AJAX response for the form choice list.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class AjaxChoiceListHelper
{
    /**
     * Generates the ajax response.
     *
     * @param Request                                                           $request
     * @param AjaxChoiceListInterface|FormBuilderInterface|FormInterface||array $choiceList
     * @param string                                                            $format
     *
     * @return Response
     *
     * @throws InvalidArgumentException When the format is not allowed
     */
    public static function generateResponse(Request $request, $choiceList, $format = 'json')
    {
        $formats = array('xml', 'json');

        if (!in_array($format, $formats)) {
            $msg = "The '%s' format is not allowed. Try with '%s'";
            throw new InvalidArgumentException(sprintf($msg, $format, implode("', '", $formats)));
        }

        if ($choiceList instanceof FormInterface) {
            $choiceList = $choiceList->getConfig();
        }

        if ($choiceList instanceof FormBuilderInterface) {
            $choiceList = $choiceList->getAttribute('choice_list', $choiceList->getOption('choice_list'));
        }

        if (!$choiceList instanceof AjaxChoiceListInterface) {
            $choiceList = new AjaxSimpleChoiceList($choiceList);
        }

        $data = static::getData($request, $choiceList);

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/' . $format);
        $response->setContent($serializer->serialize($data, $format));

        return $response;
    }

    /**
     * Gets the ajax data.
     *
     * @param Request                 $request
     * @param AjaxChoiceListInterface $choiceList
     *
     * @return array
     */
    protected static function getData(Request $request, AjaxChoiceListInterface $choiceList)
    {
        $ajaxIds = $request->get('ids', '');

        if (in_array($ajaxIds, array(null, ''))) {
            $ajaxIds = array();

        } else {
            $ajaxIds = explode(',', $ajaxIds);
        }

        $choiceList->setAjax(true);
        $choiceList->setExtractValues(true);
        $choiceList->setPageSize($request->get('ps', $choiceList->getPageSize()));
        $choiceList->setPageNumber($request->get('pn', $choiceList->getPageNumber()));
        $choiceList->setSearch($request->get('s', ''));
        $choiceList->setIds($ajaxIds);

        return array(
            'length'      => $choiceList->getSize(),
            'page_number' => $choiceList->getPageNumber(),
            'page_size'   => $choiceList->getPageSize(),
            'search'      => $choiceList->getSearch(),
            'results'     => $choiceList->getChoices(),
        );
    }
}
