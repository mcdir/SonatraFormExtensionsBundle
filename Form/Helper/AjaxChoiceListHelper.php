<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\Helper;

use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface;
use Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
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
     * @param Request                                                      $request      The request
     * @param AjaxChoiceLoaderInterface|FormBuilderInterface|FormInterface $choiceLoader The choice loader or form or array
     * @param string                                                       $format       The output format
     * @param string                                                       $prefix       The prefix of parameters
     *
     * @return Response
     *
     * @throws InvalidArgumentException When the format is not allowed
     */
    public static function generateResponse(Request $request, $choiceLoader, $format = 'json', $prefix = '')
    {
        $formats = array('xml', 'json');

        if (!in_array($format, $formats)) {
            $msg = "The '%s' format is not allowed. Try with '%s'";
            throw new InvalidArgumentException(sprintf($msg, $format, implode("', '", $formats)));
        }

        if ($choiceLoader instanceof FormBuilderInterface || $choiceLoader instanceof FormInterface) {
            $formatter = static::extractAjaxFormatter($choiceLoader);
            $choiceLoader = static::extractChoiceLoader($choiceLoader);
        } else {
            $formatter = static::createChoiceListFormatter();
        }

        if (!$choiceLoader instanceof AjaxChoiceLoaderInterface) {
            throw new UnexpectedTypeException($choiceLoader, 'Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Loader\AjaxChoiceLoaderInterface');
        }

        $data = static::getData($request, $choiceLoader, $formatter, $prefix);

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new GetSetMethodNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/'.$format);
        $response->setContent($serializer->serialize($data, $format));

        return $response;
    }

    /**
     * Gets the ajax data.
     *
     * @param Request                          $request
     * @param AjaxChoiceLoaderInterface        $choiceLoader
     * @param AjaxChoiceListFormatterInterface $formatter
     * @param string                           $prefix
     *
     * @return array
     */
    public static function getData(Request $request, AjaxChoiceLoaderInterface $choiceLoader, AjaxChoiceListFormatterInterface $formatter, $prefix = '')
    {
        $ajaxIds = $request->get($prefix.'ids', '');

        if (is_string($ajaxIds) && '' !== $ajaxIds) {
            $ajaxIds = explode(',', $ajaxIds);
        } elseif (!is_array($ajaxIds) || in_array($ajaxIds, array(null, ''))) {
            $ajaxIds = array();
        }

        $choiceLoader->setPageSize(intval($request->get($prefix.'ps', $choiceLoader->getPageSize())));
        $choiceLoader->setPageNumber(intval($request->get($prefix.'pn', $choiceLoader->getPageNumber())));
        $choiceLoader->setSearch($request->get($prefix.'s', ''));
        $choiceLoader->setIds($ajaxIds);
        $choiceLoader->reset();

        return $formatter->formatResponseData($choiceLoader);
    }

    /**
     * Extracts the ajax choice loader.
     *
     * @param FormBuilderInterface|FormInterface $form
     *
     * @return AjaxChoiceLoaderInterface
     *
     * @throws InvalidArgumentException When the choice list is not an instance of AjaxChoiceListInterface
     */
    protected static function extractChoiceLoader($form)
    {
        $form = static::getForm($form);
        $choiceLoader = $form->getAttribute('choice_loader', $form->getOption('choice_loader'));

        return $choiceLoader;
    }

    /**
     * Extracts the ajax formatter.
     *
     * @param FormBuilderInterface|FormInterface $form
     *
     * @return AjaxChoiceListFormatterInterface
     *
     * @throws InvalidArgumentException When the ajax_formatter is not an instance of AjaxChoiceListFormatterInterface
     */
    protected static function extractAjaxFormatter($form)
    {
        $form = static::getForm($form);
        $formatter = $form->getAttribute('select2', $form->getOption('select2'));
        $formatter = $formatter['ajax_formatter'];

        if (!$formatter instanceof AjaxChoiceListFormatterInterface) {
            throw new UnexpectedTypeException($formatter, 'Sonatra\Bundle\FormExtensionsBundle\Form\ChoiceList\Formatter\AjaxChoiceListFormatterInterface');
        }

        return $formatter;
    }

    /**
     * Get the form builder.
     *
     * @param mixed $value The form
     *
     * @return FormBuilderInterface
     */
    protected static function getForm($value)
    {
        return $value instanceof FormInterface
            ? $value->getConfig()
            : $value;
    }

    /**
     * Creates the choice list formatter.
     *
     * @return AjaxChoiceListFormatterInterface
     *
     * @throws InvalidArgumentException When this method is not override
     */
    protected static function createChoiceListFormatter()
    {
        throw new InvalidArgumentException('You must create a child class of AjaxChoiceLostHelper and override the "createChoiceListFormatter" method');
    }
}
