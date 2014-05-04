/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global jQuery*/
(function ($) {
    'use strict';

    // DATETIME PICKER CLASS DEFINITION
    // ================================

    $.fn.datetimePicker.Constructor.LANGUAGES = $.extend({}, $.fn.datetimePicker.Constructor.LANGUAGES, {
        fr: $.extend($.fn.datetimePicker.Constructor.LANGUAGES.en, {
            date:    'Date',
            time:    'Heure',
            cancel:  'Annuler',
            clear:   'Effacer',
            define:  'Definir'
        })
    });

}(jQuery));
