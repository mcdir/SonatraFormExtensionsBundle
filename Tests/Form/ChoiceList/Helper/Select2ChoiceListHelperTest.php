<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Tests\Form\ChoiceList\Helper;

use Sonatra\Bundle\FormExtensionsBundle\Form\Helper\AjaxChoiceListHelper;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class Select2ChoiceListHelperTest extends AjaxChoiceListHelperTest
{
    /**
     * @var string
     */
    protected $helperClass = '\Sonatra\Bundle\FormExtensionsBundle\Form\Helper\Select2ChoiceListHelper';

    public function testGenerateJsonResponseWithArray()
    {
        /* @var AjaxChoiceListHelper $helper */
        $helper = $this->helperClass;
        /* @var Request $request */
        $request = $this->request;
        $validData = '{"length":0,"page_number":1,"page_size":10,"search":"search","results":[]}';
        $response = $helper::generateResponse($request, array(), 'json');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals($validData, $response->getContent());
    }
}
