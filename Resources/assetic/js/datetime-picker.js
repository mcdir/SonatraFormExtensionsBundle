/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global jQuery*/
/*global window*/
/*global navigator*/
/*global document*/
/*global moment*/
/*global Hammer*/
/*global CSSMatrix*/
/*global WebKitCSSMatrix*/
/*global MSCSSMatrix*/
/*global DatetimePicker*/

/**
 * @param {jQuery} $
 *
 * @typedef {DatetimePicker} DatetimePicker
 * @typedef {function} moment.clone
 * @typedef {function} moment.lang
 * @typedef {function} moment.format
 */
(function ($) {
    'use strict';

    /**
     * Check if is a mobile device.
     *
     * @returns {boolean}
     *
     * @private
     */
    function mobileCheck() {
        return Boolean(navigator.userAgent.match(/Android|iPhone|iPad|iPod|IEMobile|BlackBerry|Opera Mini/i));
    }

    /**
     * Get the transform matrix of target.
     *
     * @param {jQuery} $target
     *
     * @returns {CSSMatrix|WebKitCSSMatrix|MSCSSMatrix|object}
     *
     * @private
     */
    function getTransformMatrix($target) {
        var transform = {e: 0, f: 0},
            reMatrix,
            match;

        if ($target.css('transform')) {
            if ('function' === typeof CSSMatrix) {
                transform = new CSSMatrix($target.css('transform'));

            } else if ('function' === typeof WebKitCSSMatrix) {
                transform = new WebKitCSSMatrix($target.css('transform'));

            } else if ('function' === typeof MSCSSMatrix) {
                transform = new MSCSSMatrix($target.css('transform'));

            } else {
                reMatrix = /matrix\(\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*,\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*\)/;
                match = $target.css('transform').match(reMatrix);

                if (match) {
                    transform.e = parseInt(match[1], 10);
                    transform.f = parseInt(match[2], 10);
                }
            }
        }

        return transform;
    }

    /**
     * Action on drag end transition of calendar picker.
     *
     * @param {Event} event The hammer event
     *
     * @typedef {DatetimePicker} Event.data.self The datetime picker instance
     * @typedef {string}         Event.data.type The calendar type
     *
     * @private
     */
    function dragEndCalendarTransition(event) {
        var self = event.data.self,
            type = event.data.type,
            $calendarAll = $('.dtp-body-calendar-all', self.$picker);

        $calendarAll.off('transitionend webkitTransitionEnd oTransitionEnd');
        $calendarAll.css('-webkit-transition', 'none');
        $calendarAll.css('transition', 'none');
        $calendarAll.css('-webkit-transform', '');
        $calendarAll.css('transform', '');

        switch (type) {
        case 'nextYear':
            self.nextYear(event);
            break;
        case 'previousYear':
            self.previousYear(event);
            break;
        case 'nextMonth':
            self.nextMonth(event);
            break;
        case 'previousMonth':
            self.previousMonth(event);
            break;
        default:
            break;
        }
    }

    /**
     * Init the calendar hammer instance.
     *
     * @param {DatetimePicker} self The datetime picker instance
     *
     * @private
     */
    function initCalendarSwipe(self) {
        if (!Hammer) {
            return;
        }

        var hammerCalendar = 'hammerCalendar';

        self[hammerCalendar] = new Hammer($('.dtp-body-calendar-wrapper', self.$picker).get(0), {
            swipe: false,
            transform: false,
            prevent_default: true,
            drag_lock_to_axis: true
        })

            .on('tap', $.proxy(function (event) {
                var value = $(event.target).attr('data-date-value');

                if (value) {
                    this.setDatetime(value);
                }
            }, self))

            .on('drag', $.proxy(function (event) {
                event.stopPropagation();
                event.preventDefault();

                var $calendarAll = $('.dtp-body-calendar-all', this.$picker),
                    $calendar = $('.dtp-body-calendar[data-calendar-name=current]', $calendarAll),
                    width = $calendar.outerWidth(),
                    height = $calendar.outerHeight(),
                    horizontal = 0,
                    vertical = 0;

                switch (event.gesture.direction) {
                case 'left':
                case 'right':
                    horizontal = Math.round(event.gesture.deltaX);
                    break;
                case 'up':
                case 'down':
                    vertical = Math.round(event.gesture.deltaY);
                    break;
                default:
                    break;
                }

                if (Math.abs(horizontal) > $calendar.outerWidth()) {
                    horizontal = horizontal < 0 ? -width : width;
                }

                if (Math.abs(vertical) > $calendar.outerHeight()) {
                    vertical = vertical < 0 ? -height : height;
                }

                $calendarAll.css('-webkit-transition', 'none');
                $calendarAll.css('transition', 'none');
                $calendarAll.css('-webkit-transform', 'translate3d(' + horizontal + 'px, ' + vertical + 'px, 0px)');
                $calendarAll.css('transform', 'translate3d(' + horizontal + 'px, ' + vertical + 'px, 0px)');
            }, self))

            .on('dragend', $.proxy(function () {
                var $calendarAll = $('.dtp-body-calendar-all', this.$picker),
                    $calendar = $('.dtp-body-calendar[data-calendar-name=current]', $calendarAll),
                    transform = getTransformMatrix($calendarAll),
                    horizontal = transform.e,
                    vertical = transform.f,
                    type = null;

                if (0 !== horizontal && Math.abs(horizontal) >= Math.min($calendar.outerWidth() / 3, this.options.dragDistance)) {
                    if (horizontal < 0) {
                        type = 'nextMonth';
                        horizontal = -Math.round($calendar.outerWidth());

                    } else {
                        type = 'previousMonth';
                        horizontal = Math.round($calendar.outerWidth());
                    }

                } else if (0 !== vertical && Math.abs(vertical) >= Math.min($calendar.outerHeight() / 3, this.options.dragDistance)) {
                    if (vertical < 0) {
                        type = 'nextYear';
                        vertical = -Math.round($calendar.outerHeight());

                    } else {
                        type = 'previousYear';
                        vertical = Math.round($calendar.outerHeight());
                    }

                } else {
                    $calendarAll.css('-webkit-transition', '');
                    $calendarAll.css('transition', '');
                    $calendarAll.css('-webkit-transform', '');
                    $calendarAll.css('transform', '');

                    return;
                }

                $calendarAll.on('transitionend webkitTransitionEnd oTransitionEnd', null, {'type': type, 'self': this}, dragEndCalendarTransition);
                $calendarAll.css('-webkit-transition', '');
                $calendarAll.css('transition', '');
                $calendarAll.css('-webkit-transform', 'translate3d(' + horizontal + 'px, ' + vertical + 'px, 0px)');
                $calendarAll.css('transform', 'translate3d(' + horizontal + 'px, ' + vertical + 'px, 0px)');

            }, self));
    }

    /**
     * Destroy the calendar hammer instance.
     *
     * @param {DatetimePicker} self The datetime picker instance
     *
     * @private
     */
    function destroyCalendarSwipe(self) {
        if (!Hammer) {
            return;
        }

        var hammerCalendar = 'hammerCalendar';

        delete self[hammerCalendar];
    }

    /**
     * Binding actions of keyboard.
     *
     * @param {jQuery.Event|Event} event
     *
     * @typedef {DatetimePicker} Event.data The datetime picker instance
     *
     * @private
     */
    function keyboardAction(event) {
        if (!event instanceof jQuery.Event) {
            return;
        }

        var self = event.data;

        if (event.keyCode  === 9) {// tab
            self.toggle(event);

        } else if (self.isOpen()) {// on opened picker
            if (event.keyCode === 27) {// escape
                self.close();

            } else if (event.keyCode === 13) {// enter
                self.defineValue();
                event.preventDefault();
                event.stopPropagation();

            } else {// refresh value
                self.refreshValue();
            }

        } else {// on closed picked
            if (event.keyCode === 40) {
                self.open();
            }
        }
    }

    /**
     * Close the sidebar since external action.
     *
     * @param {jQuery.Event|Event} event
     *
     * @typedef {DatetimePicker} Event.data The datetime picker instance
     *
     * @private
     */
    function closeExternal(event) {
        var self = event.data,
            $target = $(event.currentTarget.activeElement);

        if ($target.hasClass(self.options.classOpen) || $(event.target).hasClass(self.options.classWrapper) || $(event.target).parents('.' + self.options.classWrapper).size() > 0) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        self.close();
    }

    /**
     * Prevents the default event.
     *
     * @param {jQuery.Event|Event} event
     *
     * @private
     */
    function blockEvent(event) {
        event.preventDefault();
    }

    /**
     * Prevents the scroll event.
     *
     * @param {jQuery.Event|Event} event
     *
     * @returns {boolean}
     *
     * @typedef {DatetimePicker} Event.data The datetime picker instance
     *
     * @private
     */
    function preventScroll(event) {
        var self = event.data,
            state = true,
            scrollTop = self.$picker.get(0).scrollTop,
            scrollHeight = self.$picker.get(0).scrollHeight,
            height = self.$picker.height(),
            delta = (event.type === 'DOMMouseScroll' ?
                    event.originalEvent.detail * -40 :
                    event.originalEvent.wheelDelta),
            up = delta > 0;

        if (!up && -delta > scrollHeight - height - scrollTop) {
            self.$picker.scrollTop(scrollHeight);

            event.stopPropagation();
            event.preventDefault();

            state = false;

        } else if (up && delta > scrollTop) {
            self.$picker.scrollTop(0);

            event.stopPropagation();
            event.preventDefault();

            state = false;
        }

        return state;
    }

    /**
     * Action on scroll event.
     *
     * @param {DatetimePicker} self  The datetime picker instance
     * @param {string}         type  The timer type (hour, minute, second)
     *
     * @private
     */
    function selectTimeAction(self, type) {
        var $wrapper = $('.dtp-body-time-wrapper', self.$picker);

        $wrapper
            .removeClass('time-hours-selected')
            .removeClass('time-minutes-selected')
            .removeClass('time-seconds-selected')
            .addClass('time-' + type + 's-selected');

        self.refreshTimePicker();
    }

    /**
     * Action on scroll event.
     *
     * @param {DatetimePicker}     self  The datetime picker instance
     * @param {jQuery.Event|Event} event
     * @param {string}             type  The timer type (hour, minute, second)
     *
     * @private
     */
    function scrollAction(self, event, type) {
        var delta = (event.type === 'DOMMouseScroll' ?
                    event.originalEvent.detail * -40 :
                    event.originalEvent.wheelDelta);

        if (delta > 0) {
            switch (type) {
            case 'hour':
                self.previousHour();
                break;
            case 'minute':
                self.previousMinute();
                break;
            case 'second':
                self.previousSecond();
                break;
            default:
                break;
            }
        } else {
            switch (type) {
            case 'hour':
                self.nextHour();
                break;
            case 'minute':
                self.nextMinute();
                break;
            case 'second':
                self.nextSecond();
                break;
            default:
                break;
            }
        }

        event.stopPropagation();
        event.preventDefault();
    }

    /**
     * Action on scroll event for year picker.
     *
     * @param {jQuery.Event|Event} event
     *
     * @typedef {DatetimePicker} Event.data The datetime picker instance
     *
     * @private
     */
    function scrollYear(event) {
        scrollAction(event.data, event, 'year');
    }

    /**
     * Action on scroll event for month picker.
     *
     * @param {jQuery.Event|Event} event
     *
     * @typedef {DatetimePicker} Event.data The datetime picker instance
     *
     * @private
     */
    function scrollMonth(event) {
        scrollAction(event.data, event, 'month');
    }

    /**
     * Action on scroll event for hour picker.
     *
     * @param {jQuery.Event|Event} event
     *
     * @typedef {DatetimePicker} Event.data The datetime picker instance
     *
     * @private
     */
    function scrollHour(event) {
        scrollAction(event.data, event, 'hour');
    }

    /**
     * Action on scroll event for minute picker.
     *
     * @param {jQuery.Event|Event} event
     *
     * @typedef {DatetimePicker} Event.data The datetime picker instance
     *
     * @private
     */
    function scrollMinute(event) {
        scrollAction(event.data, event, 'minute');
    }

    /**
     * Action on scroll event for second picker.
     *
     * @param {jQuery.Event|Event} event
     *
     * @typedef {DatetimePicker} Event.data The datetime picker instance
     *
     * @private
     */
    function scrollSecond(event) {
        scrollAction(event.data, event, 'second');
    }

    /**
     * Action on scroll event for meridiem picker.
     *
     * @param {jQuery.Event|Event} event
     *
     * @typedef {DatetimePicker} Event.data The datetime picker instance
     *
     * @private
     */
    function scrollMeridiem(event) {
        event.data.toggleMeridiem();
        event.stopPropagation();
        event.preventDefault();
    }

    /**
     * Generate the week days.
     *
     * @param {DatetimePicker} self
     *
     * @private
     */
    function generateWeekdays(self) {
        var lang = moment.langData(self.options.locale),
            wekkdaysMin = '_weekdaysMin',
            week = '_week',
            days = lang[wekkdaysMin].slice(),
            startDay = moment.langData(self.options.locale)[week].dow,
            endDays = days.splice(0, startDay),
            $days = $('.dtp-body-header-days', self.$picker),
            i;

        days = days.concat(endDays);
        $days.empty();

        for (i = 0; i < days.length; i += 1) {
            $days.append('<div class="dtp-body-header-day" data-day-id="' + i + '">' + days[i] + '</div>');
        }
    }

    /**
     * Generate the calendar picker.
     *
     * @param {DatetimePicker} self
     * @param {string}         name
     * @param {moment}         date
     *
     * @return jQuery The calendar element
     *
     * @private
     */
    function generateCalendar(self, name, date) {
        var today,
            startDay,
            currentDay,
            $calendar,
            $week,
            number,
            dayClass,
            $day,
            i,
            j;

        today = moment();
        today.lang(self.options.locale);
        startDay = date.clone().startOf('month');
        currentDay = startDay.clone();
        $calendar = $('<div class="dtp-body-calendar" data-calendar-name="' + name + '"></div>');

        if (1 === currentDay.clone().startOf('week').date()) {
            startDay.add('days', -7);
            currentDay.add('days', -7);
        }

        currentDay.startOf('week');
        currentDay.hours(date.hours());
        currentDay.minutes(date.minutes());
        currentDay.seconds(date.seconds());

        for (i = 0; i < 6; i += 1) {
            $week = $('<div class="dtp-body-calendar-week"></div>');

            for (j = 0; j < 7; j += 1) {
                number = currentDay.date();
                dayClass = 'dtp-body-calendar-day';

                if (currentDay.year() === date.year() && currentDay.months() === date.months()) {
                    if (number === date.date()) {
                        dayClass += ' dtp-day-selected';
                    }

                    if (number === today.date() && date.months() === today.months() && date.year() === today.year()) {
                        dayClass += ' dtp-day-today';
                    }

                } else {
                    dayClass += ' dtp-day-out';
                }

                $day = $('<div class="' + dayClass + '"><div class="dtp-body-calendar-day-value" data-date-value="' + currentDay.format(self.options.format) + '">' + number + '</div></div>');

                $week.append($day);

                currentDay.add('days', 1);
            }

            $calendar.append($week);
        }

        return $calendar;
    }

    /**
     * Generate the calendar pickers (current, previous month, next month,
     * previous year and next year).
     *
     * @param {DatetimePicker} self
     * @param {moment}         date
     *
     * @returns {object} The list of calendar
     *
     * @private
     */
    function generateCalendars(self, date) {
        var $calendars,
            $calendarCurrent,
            $calendarPreviousMonth,
            $calendarNextMonth,
            $calendarPreviousYear,
            $calendarNextYear;

        $calendars = $('<div class="dtp-body-calendar-all"></div>');

        $calendarCurrent = generateCalendar(self, 'current', date);
        $calendars.append($calendarCurrent);

        $calendarPreviousMonth = generateCalendar(self, 'previous-month', date.clone().add('month', -1));
        $calendarPreviousMonth.css('-webkit-transform', 'translate3d(-100%, 0px, 0px)');
        $calendarPreviousMonth.css('transform', 'translate3d(-100%, 0px, 0px)');
        $calendars.append($calendarPreviousMonth);

        $calendarNextMonth = generateCalendar(self, 'next-month', date.clone().add('month', 1));
        $calendarNextMonth.css('-webkit-transform', 'translate3d(100%, 0px, 0px)');
        $calendarNextMonth.css('transform', 'translate3d(100%, 0px, 0px)');
        $calendars.append($calendarNextMonth);

        $calendarPreviousYear = generateCalendar(self, 'previous-year', date.clone().add('year', -1));
        $calendarPreviousYear.css('-webkit-transform', 'translate3d(0px, -100%, 0px)');
        $calendarPreviousYear.css('transform', 'translate3d(0px, -100%, 0px)');
        $calendars.append($calendarPreviousYear);

        $calendarNextYear = generateCalendar(self, 'next-year', date.clone().add('year', 1));
        $calendarNextYear.css('-webkit-transform', 'translate3d(0px, 100%, 0px)');
        $calendarNextYear.css('transform', 'translate3d(0px, 100%, 0px)');
        $calendars.append($calendarNextYear);

        return $calendars;
    }

    /**
     * Formats the knob value.
     *
     * @param {number} min           The min value
     * @param {number} max           The max value
     * @param {number} step          The step
     * @param {number} value         The value
     * @param {number} previousValue The previous value
     *
     * @returns {number} The formatted value
     *
     * @private
     */
    function knobChangeValue(min, max, step, value, previousValue) {
        var maxValue = max + 1,
            maxWithStep = (maxValue - step),
            modulo = value % step;

        value -= value % step;

        if (modulo >= (step / 2)) {
            value += step;
        }

        if (previousValue === maxValue) {
            previousValue = min;
        }

        if (value === maxValue) {
            value = min;
        }

        if (previousValue === maxWithStep && value === min) {
            // next meridiem
            value += maxValue;
        } else if (value === maxWithStep && previousValue === min) {
            // previous meridiem
            value -= maxValue;
        }

        return value;
    }

    /**
     * Generate the timer picker.
     *
     * @param {DatetimePicker} self
     *
     * @private
     */
    function generateTimer(self) {
        var knobConfig,
            knobSize,
            $wrapper,
            $display,
            $displayMeridiem,
            $hours,
            $minutes,
            $seconds,
            knobChangeHour,
            knobChangeMinute,
            knobChangeSecond;

        $wrapper = $('.dtp-body-time-wrapper', self.$picker);
        $display = $('.dtp-body-time-display', $wrapper);
        $displayMeridiem = $('.dtp-body-time-display-meridiem', $wrapper);
        $hours = $('.dtp-body-time-content-value-hours', $wrapper);
        $minutes = $('.dtp-body-time-content-value-minutes', $wrapper);
        $seconds = $('.dtp-body-time-content-value-seconds', $wrapper);
        knobSize = $wrapper.innerHeight() - parseInt($wrapper.css('padding-top'), 10) - parseInt($wrapper.css('padding-bottom'), 10);

        if (self.options.withMinutes) {
            $wrapper.addClass('time-has-minutes');
        }

        if (self.options.withSeconds) {
            $wrapper.addClass('time-has-seconds');
        }

        $wrapper.addClass('time-has-meridiem');

        if (!$wrapper.hasClass('time-hours-selected')
                && !$wrapper.hasClass('time-minutes-selected')
                && !$wrapper.hasClass('time-seconds-selected')) {
            $wrapper.addClass('time-hours-selected');
        }

        knobConfig = {
            'displayInput':    false,
            'displayPrevious': true,
            'cursor':          1,
            'lineCap':         'round',
            'width':           knobSize,
            'height':          knobSize,
            'thickness':       0.2
        };

        knobChangeHour = function (value) {
            var opts = self.options;
            value = knobChangeValue(opts.hourMin, opts.hourMax, opts.hourStep, value, parseInt($hours.val(), 10));

            // convert to 24h
            if (self.currentDate.hour() >= 12) {
                value += 12;
            }

            self.setHour(value);
        };

        knobChangeMinute = function (value) {
            var opts = self.options;
            value = knobChangeValue(opts.minuteMin, opts.minuteMax, opts.minuteStep, value, parseInt($minutes.val(), 10));

            self.setMinute(value);
        };

        knobChangeSecond = function (value) {
            var opts = self.options;
            value = knobChangeValue(opts.secondMin, opts.secondMax, opts.secondStep, value, parseInt($seconds.val(), 10));

            self.setSecond(value);
        };

        // hours
        $hours.knob($.extend(knobConfig, {
            'min':     self.options.hourMin,
            'max':     self.options.hourMax + 1,
            'step':    self.options.hourStep,
            'change':  function (value) {
                knobChangeHour(value);
            },
            'release': function (value) {
                if (!self.onDragKnob) {
                    knobChangeHour(value);
                }
            }
        }));

        // minutes
        $minutes.knob($.extend(knobConfig, {
            'min':     self.options.minuteMin,
            'max':     self.options.minuteMax + 1,
            'step':    self.options.minuteStep,
            'change':  function (value) {
                knobChangeMinute(value);
            },
            'release': function (value) {
                if (!self.onDragKnob) {
                    knobChangeMinute(value);
                }
            }
        }));

        // seconds
        $seconds.knob($.extend(knobConfig, {
            'min':     self.options.secondMin,
            'max':     self.options.secondMax + 1,
            'step':    self.options.secondStep,
            'change':  function (value) {
                knobChangeSecond(value);
            },
            'release': function (value) {
                if (!self.onDragKnob) {
                    knobChangeSecond(value);
                }
            }
        }));

        // time and meridiem display position
        $display.css('top', 0).css('left', 0);
        $displayMeridiem.css('top', 0).css('left', 0);
    }

    // DATETIME PICKER CLASS DEFINITION
    // ================================

    /**
     * @constructor
     *
     * @param {string|elements|object|jQuery} element
     * @param {object}                        options
     *
     * @this DatetimePicker
     */
    var DatetimePicker = function (element, options) {
        this.guid        = jQuery.guid;
        this.options     = $.extend({}, DatetimePicker.DEFAULTS, options);
        this.$element    = $(element);
        this.eventType   = 'click';
        this.focusEventType = 'click.st.datetimepicker';
        this.currentDate    = null;
        this.$picker        = null;

        if (mobileCheck()) {
            this.eventType = 'touchstart';
            this.focusEventType = 'touchend.st.datetimepicker';
        }

        if (null !== this.options.buttonId) {
            $('#' + this.options.buttonId).on('click' + '.st.datetimepicker', $.proxy(DatetimePicker.prototype.toggle, this));
        }

        if (this.options.openFocus) {
            this.$element.on(this.focusEventType, $.proxy(DatetimePicker.prototype.toggle, this));
        }

        this.$element.on('keyup.st.datetimepicker', null, this, keyboardAction);
        this.$element.attr('data-datetime-picker', 'true');
        this.$element.attr('autocomplete', 'off');
    },
        old;

    /**
     * Defaults options.
     *
     * @type {object}
     *
     * @property {string}  classWrapper
     * @property {string}  classWrapper
     * @property {string}  classOpen
     * @property {string}  classHeaderPicker
     * @property {string}  classBodyPicker
     * @property {string}  classFooterPicker
     * @property {string}  classFooterPicker
     * @property {string}  classDatePicker
     * @property {string}  classTimePicker
     * @property {string}  locale
     * @property {?string} format
     * @property {boolean} datePicker
     * @property {boolean} timePicker
     * @property {boolean} timePickerFirst
     * @property {boolean} withMinutes
     * @property {boolean} withSeconds
     * @property {?string} buttonId
     * @property {boolean} openFocus
     * @property {number}  dragDistance
     * @property {number}  inertiaVelocity
     * @property {number}  hourMin
     * @property {number}  hourMax
     * @property {number}  hourStep
     * @property {number}  minuteMin
     * @property {number}  minuteMax
     * @property {number}  minuteStep
     * @property {number}  secondMin
     * @property {number}  secondMax
     * @property {number}  secondStep
     */
    DatetimePicker.DEFAULTS = {
        classWrapper:      'datetime-picker-wrapper',
        classOpen:         'datetime-picker-open',
        classHeaderPicker: 'datetime-picker-header',
        classBodyPicker:   'datetime-picker-body',
        classFooterPicker: 'datetime-picker-footer',
        classDatePicker:   'datetime-picker-date',
        classTimePicker:   'datetime-picker-time',
        locale:            'en',
        format:            null,
        datePicker:        true,
        timePicker:        true,
        timePickerFirst:   false,
        withMinutes:       true,
        withSeconds:       false,
        buttonId:          null,
        openFocus:         true,
        dragDistance:      70,
        inertiaVelocity:   0.07,
        hourMin:           0,
        hourMax:           11,
        hourStep:          1,
        minuteMin:         0,
        minuteMax:         59,
        minuteStep:        1,
        secondMin:         0,
        secondMax:         59,
        secondStep:        1
    };

    /**
     * Defaults languages.
     *
     * @type {object}
     */
    DatetimePicker.LANGUAGES = {
        en: {
            date:    'Date',
            time:    'Time',
            hours:   'h',
            minutes: 'm',
            seconds: 's',
            cancel:  'Cancel',
            clear:   'Clear',
            define:  'Define'
        }
    };

    /**
     * Enables the picker.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.enabled = function () {
        this.$element.attr('disabled', 'disabled');
    };

    /**
     * Disables the picker.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.disabled = function () {
        this.$element.removeAttr('disabled');
    };

    /**
     * Check is the picker is disabled.
     *
     * @returns {boolean}
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.isDisabled = function () {
        return undefined !== this.$element.attr('disabled');
    };

    /**
     * Check if the picker is opened.
     *
     * @returns {boolean}
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.isOpen = function () {
        return this.$element.hasClass(this.options.classOpen);
    };

    /**
     * Toggle the picker (open or close).
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.toggle = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (this.isOpen() && (!event || !(this.$element.get(0) === event.target))) {
            this.close();

        } else {
            this.open();
        }
    };

    /**
     * Opens the picker.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.open = function () {
        if (this.isOpen() || this.isDisabled()) {
            return;
        }

        var value,
            tabSelected,
            format;

        // closes all other pickers
        $('[data-datetime-picker=true]').datetimePicker('close');

        tabSelected = this.options.datePicker ? 'date' : 'time';

        if (this.options.datePicker && this.options.timePickerFirst) {
            tabSelected = 'time';
        }

        this.$picker = $([
            '<div class="' + this.options.classWrapper + '" data-target="' + this.$element.attr('id') + '" data-tab-selected="' + tabSelected + '" data-date-picker="' + this.options.datePicker + '" data-time-picker="' + this.options.timePicker + '">',
            '<div class="' + this.options.classHeaderPicker + '">',
            '<span class="' + this.options.classHeaderPicker + '-title"></span>',
            '<div class="' + this.options.classHeaderPicker + '-tabs">',
            '<ul class="' + this.options.classHeaderPicker + '-nav-tabs">',
            '<li data-role="tab" class="' + this.options.classHeaderPicker + '-nav-tab dtp-tab-date">',
            '<span class="dtp-show-tab">' + this.langData().date + '</span>',
            '</li>',
            '<li data-role="tab" class="' + this.options.classHeaderPicker + '-nav-tab dtp-tab-time">',
            '<span class="dtp-show-tab">' + this.langData().time + '</span>',
            '</li>',
            '</ul>',
            '</div>',
            '</div>',
            '<div class="' + this.options.classBodyPicker + '">',
            '<div class="dtp-body-date">',
            '<div class="dtp-body-header">',
            '<div class="dtp-body-header-choice dtp-choice-month">',
            '<div class="dtp-body-header-choice-content">',
            '<span class="dtp-choice-btn dtp-choice-month-btn-prev"></span>',
            '<div class="dtp-choice-value">',
            '<select class="dtp-choice-value-select dtp-choice-month-value">',
            '</select>',
            '</div>',
            '<span class="dtp-choice-btn dtp-choice-month-btn-next"></span>',
            '</div>',
            '</div>',
            '<div class="dtp-body-header-choice dtp-choice-year">',
            '<div class="dtp-body-header-choice-content">',
            '<span class="dtp-choice-btn dtp-choice-year-btn-prev"></span>',
            '<div class="dtp-choice-value">',
            '<select class="dtp-choice-value-select dtp-choice-year-value">',
            '</select>',
            '</div>',
            '<span class="dtp-choice-btn dtp-choice-year-btn-next"></span>',
            '</div>',
            '</div>',
            '</div>',
            '<div class="dtp-body-header dtp-body-header-days">',
            '</div>',
            '<div class="dtp-body-calendar-wrapper">',
            '</div>',
            '</div>',
            '<div class="dtp-body-time">',
            '<div class="dtp-body-time-wrapper time-has-hours">',
            '<div class="dtp-body-time-display">',
            '<span class="dtp-body-time-display-hours"></span>',
            '<span class="dtp-body-time-display-hours-split">' + this.langData().hours + '</span>',
            '<span class="dtp-body-time-display-minutes"></span>',
            '<span class="dtp-body-time-display-minutes-split">' + this.langData().minutes + '</span>',
            '<span class="dtp-body-time-display-seconds"></span>',
            '<span class="dtp-body-time-display-seconds-split">' + this.langData().seconds + '</span>',
            '</div>',
            '<div class="dtp-body-time-display-meridiem">',
            '<span class="dtp-body-time-display-meridiem-btn"></span>',
            '</div>',
            '<div class="dtp-body-time-content">',
            '<div class="dtp-body-time-content-hours">',
            '<input type="text" class="dtp-body-time-content-value-hours">',
            '</div>',
            '<div class="dtp-body-time-content-minutes">',
            '<input type="text" class="dtp-body-time-content-value-minutes">',
            '</div>',
            '<div class="dtp-body-time-content-seconds">',
            '<input type="text" class="dtp-body-time-content-value-seconds">',
            '</div>',
            '</div>',
            '</div>',
            '</div>',
            '</div>',
            '<div class="' + this.options.classFooterPicker + '">',
            '<span class="' + this.options.classFooterPicker + '-btn dtp-btn-cancel"><span>' + this.langData().cancel + '</span></span>',
            '<span class="' + this.options.classFooterPicker + '-btn dtp-btn-clear"><span>' + this.langData().clear + '</span></span>',
            '<span class="' + this.options.classFooterPicker + '-btn dtp-btn-define"><span>' + this.langData().define + '</span></span>',
            '</div>',
            '</div>'
        ].join(''));

        $('body').append(this.$picker);

        value = this.getValue();
        format = this.options.format;

        if ('' === value) {
            this.currentDate = moment();
        } else {
            this.currentDate = moment(value, format);
        }

        this.currentDate.lang(this.options.locale);

        generateWeekdays(this);
        generateTimer(this);

        this.refreshValue();
        this.position();

        this.$element.addClass(this.options.classOpen);

        this.$picker.on('touchmove', blockEvent);
        this.$picker.on('DOMMouseScroll mousewheel', null, this, preventScroll);
        this.$picker.on(this.eventType, 'span.' + this.options.classHeaderPicker + '-title', $.proxy(DatetimePicker.prototype.setToday, this));
        this.$picker.on(this.eventType, '.dtp-btn-cancel', $.proxy(DatetimePicker.prototype.cancel, this));
        this.$picker.on(this.eventType, '.dtp-btn-clear', $.proxy(DatetimePicker.prototype.clearValue, this));
        this.$picker.on(this.eventType, '.dtp-btn-define', $.proxy(DatetimePicker.prototype.defineValue, this));
        this.$picker.on(this.eventType, '.dtp-tab-date > span.dtp-show-tab', $.proxy(DatetimePicker.prototype.showDate, this));
        this.$picker.on(this.eventType, '.dtp-tab-time > span.dtp-show-tab', $.proxy(DatetimePicker.prototype.showTime, this));
        this.$picker.on('change',       'select.dtp-choice-year-value', $.proxy(DatetimePicker.prototype.setYear, this));
        this.$picker.on(this.eventType, 'span.dtp-choice-year-btn-prev', $.proxy(DatetimePicker.prototype.previousYear, this));
        this.$picker.on(this.eventType, 'span.dtp-choice-year-btn-next', $.proxy(DatetimePicker.prototype.nextYear, this));
        this.$picker.on('change',       'select.dtp-choice-month-value', $.proxy(DatetimePicker.prototype.setMonth, this));
        this.$picker.on(this.eventType, 'span.dtp-choice-month-btn-prev', $.proxy(DatetimePicker.prototype.previousMonth, this));
        this.$picker.on(this.eventType, 'span.dtp-choice-month-btn-next', $.proxy(DatetimePicker.prototype.nextMonth, this));
        this.$picker.on(this.eventType, '.dtp-body-calendar-day > a', $.proxy(DatetimePicker.prototype.setDatetime, this));
        this.$picker.on(this.eventType, '.dtp-body-time-display-hours', $.proxy(DatetimePicker.prototype.selectHour, this));
        this.$picker.on(this.eventType, '.dtp-body-time-display-minutes', $.proxy(DatetimePicker.prototype.selectMinute, this));
        this.$picker.on(this.eventType, '.dtp-body-time-display-seconds', $.proxy(DatetimePicker.prototype.selectSecond, this));
        this.$picker.on(this.eventType, '.dtp-body-time-display-meridiem-btn', $.proxy(DatetimePicker.prototype.selectMeridiem, this));
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-year', this, scrollYear);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-month', this, scrollMonth);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-calendar-wrapper', this, scrollMonth);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-display-hours', this, scrollHour);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-display-minutes', this, scrollMinute);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-display-seconds', this, scrollSecond);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-display-meridiem-btn', this, scrollMeridiem);
        $(document).on(this.eventType + '.st.datetimepicker' + this.guid, null, this, closeExternal);
        $(window).on('resize.st.datetimepicker' + this.guid, null, this, closeExternal);
        $(window).on('keyup.st.datetimepicker' + this.guid, null, this, keyboardAction);
        $(window).on('scroll.st.datetimepicker' + this.guid, null, this, closeExternal);

        initCalendarSwipe(this);
    };

    /**
     * Closes the picker.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.close = function () {
        if (!this.isOpen() || null === this.$picker) {
            return;
        }

        this.currentDate = null;
        this.$picker.off('touchmove', blockEvent);
        this.$picker.off('DOMMouseScroll mousewheel', null, this, preventScroll);
        this.$picker.off(this.eventType, 'span.' + this.options.classHeaderPicker + '-title', $.proxy(DatetimePicker.prototype.setToday, this));
        this.$picker.off(this.eventType, '.dtp-btn-cancel', $.proxy(DatetimePicker.prototype.cancel, this));
        this.$picker.off(this.eventType, '.dtp-btn-clear', $.proxy(DatetimePicker.prototype.clearValue, this));
        this.$picker.off(this.eventType, '.dtp-btn-define', $.proxy(DatetimePicker.prototype.defineValue, this));
        this.$picker.off(this.eventType, '.dtp-tab-date > span.dtp-show-tab', $.proxy(DatetimePicker.prototype.showDate, this));
        this.$picker.off(this.eventType, '.dtp-tab-time > span.dtp-show-tab', $.proxy(DatetimePicker.prototype.showTime, this));
        this.$picker.off('change',       'select.dtp-choice-year-value', $.proxy(DatetimePicker.prototype.setYear, this));
        this.$picker.off(this.eventType, 'span.dtp-choice-year-btn-prev', $.proxy(DatetimePicker.prototype.previousYear, this));
        this.$picker.off(this.eventType, 'span.dtp-choice-year-btn-next', $.proxy(DatetimePicker.prototype.nextYear, this));
        this.$picker.off('change',       'select.dtp-choice-month-value', $.proxy(DatetimePicker.prototype.setMonth, this));
        this.$picker.off(this.eventType, 'span.dtp-choice-month-btn-prev', $.proxy(DatetimePicker.prototype.previousMonth, this));
        this.$picker.off(this.eventType, 'span.dtp-choice-month-btn-next', $.proxy(DatetimePicker.prototype.nextMonth, this));
        this.$picker.off(this.eventType, '.dtp-body-calendar-day > a', $.proxy(DatetimePicker.prototype.setDatetime, this));
        this.$picker.off(this.eventType, '.dtp-body-time-display-hours', $.proxy(DatetimePicker.prototype.selectHour, this));
        this.$picker.off(this.eventType, '.dtp-body-time-display-minutes', $.proxy(DatetimePicker.prototype.selectMinute, this));
        this.$picker.off(this.eventType, '.dtp-body-time-display-seconds', $.proxy(DatetimePicker.prototype.selectSecond, this));
        this.$picker.off(this.eventType, '.dtp-body-time-display-meridiem-btn', $.proxy(DatetimePicker.prototype.selectMeridiem, this));
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-year', scrollYear);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-month', scrollMonth);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-calendar-wrapper', scrollMonth);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-display-hours', scrollHour);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-display-minutes', scrollMinute);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-display-seconds', scrollSecond);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-display-meridiem-btn', scrollMeridiem);
        this.$picker.remove();
        this.$picker = null;
        destroyCalendarSwipe(this);
        this.$element.removeClass(this.options.classOpen);

        $(document).off(this.eventType + '.st.datetimepicker' + this.guid, null, this, closeExternal);
        $(window).off('resize.st.datetimepicker' + this.guid, null, this, closeExternal);
        $(window).off('keyup.st.datetimepicker' + this.guid, null, this, keyboardAction);
        $(window).off('scroll.st.datetimepicker' + this.guid, null, this, closeExternal);
    };

    /**
     * Refreshs the picker position.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.position = function () {
        if (null === this.$picker) {
            return;
        }

        var top = this.$element.offset().top + this.$element.outerHeight(),
            $window = $(window).eq(0),
            wTop = $window.scrollTop();

        this.$picker.css('width', this.$element.width());
        this.$picker.css('left', this.$element.offset().left);

        if ((this.$picker.outerHeight() + top - wTop) > $window.height()) {
            top = this.$element.offset().top - this.$picker.outerHeight();
        }

        if (top - wTop < 0) {
            top = this.$element.offset().top + this.$element.outerHeight();

            if (top + this.$picker.outerHeight() > $(window).height()) {
                top += $(window).height() - (top + this.$picker.outerHeight() - wTop);
            }
        }

        this.$picker.css('top', top);
    };

    /**
     * Get the language configuration.
     *
     * @param {string} locale The ISO code of language
     *
     * @returns {object} The language configuration
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.langData = function (locale) {
        if (undefined === locale) {
            locale = this.options.locale;
        }

        if (undefined === DatetimePicker.LANGUAGES[locale]) {
            locale = 'en';
        }

        return DatetimePicker.LANGUAGES[locale];
    };

    /**
     * Set value.
     *
     * @param {string|moment} date The full datetime value formatted with the default
     *                             option format.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.setValue = function (date) {
        if (typeof date === 'string') {
            /* @type {moment} */
            date = moment(date, this.options.format);
            date.lang(this.options.locale);
        }

        if (null !== date) {
            date = date.format(this.options.format);
        }

        this.$element.val(date);
    };

    /**
     * Get value.
     *
     * @returns {string} The full datetime value formatted with the default option
     *                   format.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.getValue = function () {
        return this.$element.val();
    };

    /**
     * Refresh the temporary value defined in picker (note element).
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.refreshValue = function () {
        if (null === this.currentDate) {
            return;
        }

        var value = this.getValue(),
            format = this.options.format;

        if ('' === value) {
            this.currentDate = moment();
        } else {
            this.currentDate = moment(value, format);
        }

        this.currentDate.lang(this.options.locale);

        this.refreshPicker();
    };

    /**
     * Refreshs the date and time picker blocks with the value defined in the
     * element.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.refreshPicker = function () {
        this.refreshDatePicker();
        this.refreshTimePicker();
    };

    /**
     * Refreshs the date picker blocks with the value defined in the element.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.refreshDatePicker = function () {
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        var $header = this.$picker.children('.' + this.options.classHeaderPicker).eq(0),
            $title = $header.children('.' + this.options.classHeaderPicker + '-title').eq(0),
            $body = this.$picker.children('.' + this.options.classBodyPicker).eq(0),
            $months,
            monthList,
            monthsShort = '_monthsShort',
            selectedMonth,
            selectedYear,
            $years,
            startYear,
            endYear,
            $calendarWrapper,
            i,
            j;

        // title
        $title.text(this.currentDate.format(this.options.format));

        // months list
        $months = $('.dtp-choice-month-value', $body);
        monthList = moment.langData()[monthsShort];
        $months.empty();

        for (i = 0; i < monthList.length; i += 1) {
            selectedMonth = i === this.currentDate.month() ? ' selected="selected"' : '';
            $months.append('<option value="' + i + '"' + selectedMonth + '>' + monthList[i] + '</option>');
        }

        // years list
        $years = $('.dtp-choice-year-value', $body);
        startYear = this.currentDate.clone();
        endYear = this.currentDate.clone();

        $years.empty();
        startYear = startYear.add('year', -10).year();
        endYear = endYear.add('year', 10).year();

        for (j = startYear; j <= endYear; j += 1) {
            selectedYear = (j === this.currentDate.year()) ? ' selected="selected"' : '';
            $years.append('<option value="' + j + '"' + selectedYear + '>' + j + '</option>');
        }

        // calendar
        $calendarWrapper = $('.dtp-body-calendar-wrapper', $body);
        $calendarWrapper.empty();
        $calendarWrapper.append(generateCalendars(this, this.currentDate));
    };

    /**
     * Refreshs the time picker blocks with the value defined in the element.
     *
     * @typedef {boolean} DatetimePicker.onDragKnob Check if the time picker is in drag action
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.refreshTimePicker = function () {
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        var colorRegex = /^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/,
            format = this.options.format,
            hourFormat = format.indexOf('HH') >= 0 ? 'HH' : 'H',
            minuteFormat = format.indexOf('mm') >= 0 ? 'mm' : 'm',
            secondFormat = format.indexOf('ss') >= 0 ? 'ss' : 's',
            $header = this.$picker.children('.' + this.options.classHeaderPicker).eq(0),
            $title = $header.children('.' + this.options.classHeaderPicker + '-title').eq(0),
            $wrapper = $('.dtp-body-time-wrapper', this.$picker),
            $display = $('.dtp-body-time-display', $wrapper),
            $displayHours = $('.dtp-body-time-display-hours', $display),
            $displayMinutes = $('.dtp-body-time-display-minutes', $display),
            $displaySeconds = $('.dtp-body-time-display-seconds', $display),
            $displayMeridiem = $('.dtp-body-time-display-meridiem-btn', $wrapper),
            $contentHours = $('.dtp-body-time-content-value-hours', $wrapper),
            $contentMinutes = $('.dtp-body-time-content-value-minutes', $wrapper),
            $contentSeconds = $('.dtp-body-time-content-value-seconds', $wrapper),
            $pickerHeader,
            centerPositionTop,
            centerPositionLeft,
            bg,
            hex = function (x) {
                return ("0" + parseInt(x, 10).toString(16)).slice(-2);
            };

        hourFormat = format.indexOf('hh') >= 0 ? 'hh' : hourFormat;
        hourFormat = format.indexOf('h') >= 0 ? 'h' : hourFormat;

        $title.text(this.currentDate.format(this.options.format));

        $displayHours.text(this.currentDate.format(hourFormat));
        $displayMinutes.text(this.currentDate.format(minuteFormat));
        $displaySeconds.text(this.currentDate.format(secondFormat));
        $displayMeridiem.text(moment.langData().meridiem(this.currentDate.hour(), this.currentDate.minute(), false));

        $contentHours.val(this.currentDate.hour() % 12);
        $contentMinutes.val(this.currentDate.minute());
        $contentSeconds.val(this.currentDate.second());

        this.onDragKnob = true;
        $contentHours.trigger('change');
        $contentMinutes.trigger('change');
        $contentSeconds.trigger('change');
        delete this.onDragKnob;

        if ($wrapper.hasClass('time-hours-selected')) {
            bg = $displayHours.css('background-color').match(colorRegex);
            $contentHours.trigger('configure', {
                "fgColor": "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3])
            });
        }

        if ($wrapper.hasClass('time-minutes-selected')) {
            bg = $displayMinutes.css('background-color').match(colorRegex);
            $contentMinutes.trigger('configure', {
                "fgColor": "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3])
            });
        }

        if ($wrapper.hasClass('time-seconds-selected')) {
            bg = $displaySeconds.css('background-color').match(colorRegex);
            $contentSeconds.trigger('configure', {
                "fgColor": "#" + hex(bg[1]) + hex(bg[2]) + hex(bg[3])
            });
        }

        // time and meridiem display position
        if (parseInt($display.css('top'), 10) === 0 && $wrapper.outerWidth() > 0) {
            $pickerHeader = $('.datetime-picker-header', this.$picker);
            centerPositionTop = Math.round($pickerHeader.outerHeight() + $wrapper.outerHeight() / 2);
            centerPositionLeft = Math.round($wrapper.outerWidth() / 2);

            $display
                .css('top', Math.round(centerPositionTop - $display.outerHeight() / 2))
                .css('left', Math.round(centerPositionLeft - $display.outerWidth() / 2));
            $displayMeridiem.parent()
                .css('top', Math.round(centerPositionTop + $display.outerHeight() - $displayMeridiem.outerHeight() / 2))
                .css('left', Math.round(centerPositionLeft - $displayMeridiem.outerWidth() / 2));
        }
    };

    /**
     * Close the picker without changes the value of the element.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.cancel = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        this.close();
    };

    /**
     * Removes value of the element.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.clearValue = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.setValue(null);
        this.close();
    };

    /**
     * Close the picker with changes the value of the element.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.defineValue = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null === this.currentDate || null === this.$picker || !this.currentDate.isValid()) {
            return;
        }

        this.setValue(this.currentDate);
        this.close();
    };

    /**
     * Show the date picker tab.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.showDate = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.datePicker || null === this.currentDate || null === this.$picker || 'date' === this.$picker.attr('data-tab-selected')) {
            return;
        }

        this.$picker.attr('data-tab-selected', 'date');
        this.refreshDatePicker();
        this.position();
    };

    /**
     * Show the time picker tab.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.showTime = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.timePicker || null === this.$picker || 'time' === this.$picker.attr('data-tab-selected')) {
            return;
        }

        this.$picker.attr('data-tab-selected', 'time');
        this.refreshTimePicker();
        this.position();
    };

    /**
     * Select the hour in the time picker tab.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.selectHour = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.timePicker || null === this.$picker || 'time' !== this.$picker.attr('data-tab-selected')) {
            return;
        }

        selectTimeAction(this, 'hour');
    };

    /**
     * Select the minute in the time picker tab.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.selectMinute = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.timePicker || null === this.$picker || 'time' !== this.$picker.attr('data-tab-selected')) {
            return;
        }

        selectTimeAction(this, 'minute');
    };

    /**
     * Select the second in the time picker tab.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.selectSecond = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.timePicker || null === this.$picker || 'time' !== this.$picker.attr('data-tab-selected')) {
            return;
        }

        selectTimeAction(this, 'second');
    };

    /**
     * Select the meridiem in the time picker tab.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.selectMeridiem = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.timePicker || null === this.$picker || 'time' !== this.$picker.attr('data-tab-selected')) {
            return;
        }

        this.toggleMeridiem();
    };

    /**
     * Set the full datetime value in temporary picker value.
     *
     * @param {string|jQuery.Event|Event} datetime The full datetime value formatted with the default
     *                                             option format.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.setDatetime = function (datetime) {
        if (datetime instanceof jQuery.Event) {
            datetime.preventDefault();
            datetime.stopPropagation();
            datetime = $(datetime.target).attr('data-date-value');
        }

        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        if (typeof datetime === 'string') {
            datetime = moment(datetime, this.options.format);
        }

        this.currentDate = datetime;
        this.currentDate.lang(this.options.locale);
        this.refreshPicker();
    };

    /**
     * Set the full today datetime value in temporary picker value.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.setToday = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        this.setDatetime(moment());
    };

    /**
     * Set the year in temporary picker value.
     *
     * @param {number|jQuery.Event|Event} year
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.setYear = function (year) {
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        if (year instanceof jQuery.Event) {
            year = $(year.target).val();
        }

        this.currentDate.year(year);
        this.refreshDatePicker();
    };

    /**
     * Set the previous year in temporary picker value.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.previousYear = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('year', -1);
        this.refreshDatePicker();
    };

    /**
     * Set the next year in temporary picker value.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.nextYear = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('year', 1);
        this.refreshDatePicker();
    };

    /**
     * Set the month in temporary picker value.
     *
     * @param {number|jQuery.Event|Event} month
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.setMonth = function (month) {
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        if (month instanceof jQuery.Event) {
            month = $(month.target).val();
        }

        this.currentDate.month(parseInt(month, 10));
        this.refreshDatePicker();
    };

    /**
     * Set the previous month in temporary picker value.
     *
     * @param {jQuery.Event|Event} event
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.previousMonth = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('month', -1);
        this.refreshDatePicker();
    };

    /**
     * Set the next month in temporary picker value.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.nextMonth = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('month', 1);
        this.refreshDatePicker();
    };

    /**
     * Set the hour in temporary picker value.
     *
     * @param {number} hour
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.setHour = function (hour) {
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.hour(hour);
        this.refreshTimePicker();
    };

    /**
     * Set the previous hour in temporary picker value.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.previousHour = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('hour', -this.options.hourStep);
        this.refreshTimePicker();
    };

    /**
     * Set the next hour in temporary picker value.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.nextHour = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('hour', this.options.hourStep);
        this.refreshTimePicker();
    };

    /**
     * Set the minute in temporary picker value.
     *
     * @param {number} minute
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.setMinute = function (minute) {
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.minute(minute);
        this.refreshTimePicker();
    };

    /**
     * Set the previous minute in temporary picker value.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.previousMinute = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.withMinutes || null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('minute', -this.options.minuteStep);
        this.refreshTimePicker();
    };

    /**
     * Set the next minute in temporary picker value.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.nextMinute = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.withMinutes || null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('minute', this.options.minuteStep);
        this.refreshTimePicker();
    };

    /**
     * Set the second in temporary picker value.
     *
     * @param {number} second
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.setSecond = function (second) {
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.second(second);
        this.refreshTimePicker();
    };

    /**
     * Set the previous second in temporary picker value.
     *
     * @param  {jQuery.Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.previousSecond = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.withSeconds || null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('second', -this.options.secondStep);
        this.refreshTimePicker();
    };

    /**
     * Set the next second in temporary picker value.
     *
     * @param {jQuery.Event|Event} [event]
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.nextSecond = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.withSeconds || null === this.currentDate || null === this.$picker) {
            return;
        }

        this.currentDate.add('second', this.options.secondStep);
        this.refreshTimePicker();
    };

    /**
     * Set the meridiem in temporary picker value.
     *
     * @param {string} meridiem The meridiem am/pm
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.setMeridiem = function (meridiem) {
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        meridiem = meridiem.toLowerCase();

        if (this.currentDate.hours() >= 12 && 'am' === meridiem) {
            this.currentDate.add('hour', -12);

        } else if (this.currentDate.hours() < 12 && 'pm' === meridiem) {
            this.currentDate.add('hour', 12);

        } else {
            return;
        }

        this.refreshTimePicker();
    };

    /**
     * Toggles the meridiem in temporary picker value.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.toggleMeridiem = function () {
        if (null === this.currentDate) {
            return;
        }

        if (this.currentDate.hours() >= 12) {
            this.setMeridiem('am');

        } else {
            this.setMeridiem('pm');
        }
    };

    /**
     * Destroy instance.
     *
     * @this DatetimePicker
     */
    DatetimePicker.prototype.destroy = function () {
        this.close();

        if (null !== this.options.buttonId) {
            $('#' + this.options.buttonId).off('click' + '.st.datetimepicker', $.proxy(DatetimePicker.prototype.toggle, this));
        }

        if (this.options.openFocus) {
            this.$element.on(this.focusEventType, $.proxy(DatetimePicker.prototype.toggle, this));
        }

        this.$element.removeData('st.datetimepicker');
    };


    // DATETIME PICKER PLUGIN DEFINITION
    // =================================

    old = $.fn.datetimePicker;

    /**
     * @class datetimePicker
     *
     * @param {string|object} option
     * @param {*}             [value]
     *
     * @returns {jQuery}
     * @this jQuery
     *
     * @memberOf jQuery.fn
     */
    $.fn.datetimePicker = function (option, value) {
        return $(this).each(function () {
            var $this   = $(this),
                data    = $this.data('st.datetimepicker'),
                options = typeof option === 'object' && option;

            if (!data && option === 'destroy') {
                return;
            }

            if (!data) {
                $this.data('st.datetimepicker', (data = new DatetimePicker(this, options)));
            }

            if (typeof option === 'string') {
                data[option](value);
            }
        });
    };

    $.fn.datetimePicker.Constructor = DatetimePicker;


    // DATETIME PICKER NO CONFLICT
    // ===========================

    $.fn.datetimePicker.noConflict = function () {
        $.fn.datetimePicker = old;

        return this;
    };


    // DATETIME PICKER DATA-API
    // ========================

    $(window).on('load', function () {
        $('[data-datetime-picker="true"]').each(function () {
            var $this = $(this);
            $this.datetimePicker($this.data());
        });
    });

}(jQuery));
