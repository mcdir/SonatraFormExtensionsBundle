<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Profiler\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sonatra\Bundle\FormExtensionsBundle\Form\FormRendererEngineTraceableInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class FormDataCollector extends DataCollector
{
    /**
     * @var FormRendererEngineTraceableInterface
     */
    private $engine;

    /**
     * Constructor.
     *
     * @param FormRendererEngineTraceableInterface $engine
     */
    public function __construct(FormRendererEngineTraceableInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $traces = $this->engine->getTraces();
        $total = count($traces);
        $children = 0;
        $duration = 0;

        foreach ($traces as $trace) {
            if (!$trace['is_master']) {
                $children += 1;
            }

            $duration += $trace['duration'];
        }

        $this->data = array(
                'forms'   => $traces,
                'total'    => $total,
                'master'   => $total - $children,
                'children' => $children,
                'duration' => $duration,
        );
    }

    /**
     * Get forms.
     *
     * @return \Symfony\Component\Form\FormView[]
     */
    public function getForms()
    {
        return $this->data['forms'];
    }

    /**
     * Get Total form.
     *
     * @return int
     */
    public function getSize()
    {
        return $this->data['total'];
    }

    /**
     * Get total form.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->data['total'];
    }

    /**
     * Get total children form.
     *
     * @return int
     */
    public function getChildrenSize()
    {
        return $this->data['children'];
    }

    /**
     * Get total master form.
     *
     * @return int
     */
    public function getMasterSize()
    {
        return $this->data['master'];
    }

    /**
     * Get duration.
     *
     * @return int
     */
    public function getDuration()
    {
        return $this->data['duration'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sonatra_form_extensions';
    }
}
