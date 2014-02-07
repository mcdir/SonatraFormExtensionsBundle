/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

+function ($) {
    'use strict';

    // DATETIME PICKER CLASS DEFINITION
    // ================================

    $.fn.datetimepicker.Constructor.LANGUAGES = $.extend({}, $.fn.datetimepicker.Constructor.LANGUAGES, {
        fr: {
            date:    'Date',
            time:    'Heure',
            hours:   'Heures',
            minutes: 'Minutes',
            seconds: 'Secondes',
            cancel:  'Annuler',
            clear:   'Effacer',
            define:  'Definir'
        }
    });

}(jQuery);
