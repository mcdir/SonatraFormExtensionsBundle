<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\FormExtensionsBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class DateTimeJqueryTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['time_only'] = $options['time_only'];

        $format = $options['format'];
        $format = str_replace('M', 'm', $format);
        $format = str_replace('yyyy', 'yy', $format);
        $format = str_replace('a', 'TT', $format);
        $pos = strpos($format, ' ');

        if (false !== $pos) {
            $view->vars['date_format']       = substr($format, 0, $pos);
            $view->vars['time_format']       = substr($format, $pos + 1);

        } else {
            $view->vars['date_format']       = $options['time_only'] ? '' : $format;
            $view->vars['time_format']       = $options['time_only'] ? $format : '';
        }

        $view->vars['with_seconds']      = $options['with_seconds'] ? 'true' : 'false';
        $view->vars['time_only']         = $options['time_only'] ? 'true' : 'false';
        $view->vars['show_timepicker']   = $options['show_timepicker'] ? 'true' : 'false';

        $view->vars['show_timezone']     = $options['show_timezone'];
        $view->vars['show_time']         = $options['show_time'];
        $view->vars['step_hour']         = $options['step_hour'];
        $view->vars['step_minute']       = $options['step_minute'];
        $view->vars['step_second']       = $options['step_second'];
        $view->vars['hour']              = $options['hour'];
        $view->vars['minute']            = $options['minute'];
        $view->vars['second']            = $options['second'];
        $view->vars['timezone']          = $options['timezone'];
        $view->vars['hour_min']          = $options['hour_min'];
        $view->vars['minute_min']        = $options['minute_min'];
        $view->vars['second_min']        = $options['second_min'];
        $view->vars['hour_max']          = $options['hour_max'];
        $view->vars['minute_max']        = $options['minute_max'];
        $view->vars['second_max']        = $options['second_max'];
        $view->vars['show_button_panel'] = $options['show_button_panel'];
        $view->vars['min_date_time']     = $options['min_date_time'];
        $view->vars['max_date_time']     = $options['max_date_time'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $format = function (Options $options) {
            $date_format = \IntlDateFormatter::NONE;
            $time_format = \IntlDateFormatter::NONE;

            $med = \IntlDateFormatter::MEDIUM;
            $short = \IntlDateFormatter::SHORT;

            if (!$options['time_only'] ||
                    (!$options['show_timepicker'] && $options['time_only'])) {
                $date_format = $short;
            }

            if ($options['show_timepicker']) {
                $time_format = $options['with_seconds'] ? $med : $short;
            }

            $formater = new \IntlDateFormatter(
                    $options['locale'],
                    $date_format,
                    $time_format,
                    $options['user_timezone'],
                    \IntlDateFormatter::GREGORIAN,
                    null
            );

            $formater->setLenient(false);
            $pattern = $formater->getPattern();
            $pattern = str_replace('yy', 'yyyy', $pattern);

            return $pattern;
        };

        $resolver->setDefaults(array(
                'widget'            => 'single_text',
                'time_only'         => false,
                'show_timepicker'   => true,
                'locale'            => \Locale::getDefault(),
                'user_timezone'     => null,
                'format'            => $format,
                'show_timezone'     => false,
                'show_time'         => true,
                'step_hour'         => 1,
                'step_minute'       => 1,
                'step_second'       => 1,
                'hour'              => 0,
                'minute'            => 0,
                'second'            => 0,
                'timezone'          => 0,
                'hour_min'          => 0,
                'minute_min'        => 0,
                'second_min'        => 0,
                'hour_max'          => 23,
                'minute_max'        => 59,
                'second_max'        => 59,
                'show_button_panel' => true,
                'min_date_time'     => null,
                'max_date_time'     => null,

                // override parent type value (merge options for datetime, date, time)
                'empty_value'       => null,
                'with_minutes'      => true,
                'with_seconds'      => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'datetime';
    }
}
