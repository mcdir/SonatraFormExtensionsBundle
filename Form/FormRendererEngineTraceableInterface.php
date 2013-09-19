<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form;

use Symfony\Component\Form\FormRendererEngineInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Adapter for rendering form templates with a profiler access.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface FormRendererEngineTraceableInterface extends FormRendererEngineInterface
{
    /**
     * Set stopwatch.
     *
     * @param Stopwatch $stopwatch
     */
    public function setStopWatch(Stopwatch $stopwatch = null);

    /**
     * Get all block traces for profiling.
     *
     * @return array
    */
    public function getTraces();
}
