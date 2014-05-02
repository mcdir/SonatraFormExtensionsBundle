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
     * Action on drag of timer picker.
     *
     * @param {DatetimePicker} self The datetime picker instance
     * @param {string}         type The timer type (hour, minute second)
     *
     * @private
     */
    function dragTimerAction(self, type) {
        if (!Hammer) {
            return;
        }

        var name = 'hammerTimer' + type.charAt(0).toUpperCase() + type.slice(1),
            startPositionName = 'hammerStartPosition' + type.charAt(0).toUpperCase() + type.slice(1);

        self[name] = new Hammer($('.dtp-body-time-content-' + type, self.$picker).get(0), {
            swipe: false,
            transform: false,
            prevent_default: true,
            drag_lock_to_axis: true
        })

            .on('dragstart', $.proxy(function (startPositionName, event) {
                var $timerSelector = $(event.currentTarget),
                    $timerAll = $('.dtp-body-time-content-seletor-all', $timerSelector);

                $timerSelector.addClass('dtp-timer-on-drag');
                this[startPositionName] = getTransformMatrix($timerAll);
            }, self, startPositionName))

            .on('drag', $.proxy(function (startPositionName, event) {
                /* @type {jQuery} */
                var $timerAll = $('.dtp-body-time-content-seletor-all', event.currentTarget),
                    vertical = 0,
                    itemHeight = Math.round($timerAll.outerHeight() / $timerAll.children().size());

                switch (event.gesture.direction) {
                case 'up':
                case 'down':
                    vertical = Math.round(event.gesture.deltaY + this[startPositionName].f);
                    break;
                default:
                    break;
                }

                if (vertical > itemHeight) {
                    vertical = itemHeight;

                } else if (vertical < -$timerAll.outerHeight()) {
                    vertical = -$timerAll.outerHeight();
                }

                $timerAll.css('-webkit-transition', 'none');
                $timerAll.css('transition', 'none');
                $timerAll.css('-webkit-transform', 'translate3d(0px, ' + vertical + 'px, 0px)');
                $timerAll.css('transform', 'translate3d(0px, ' + vertical + 'px, 0px)');
            }, self, startPositionName))

            .on('dragend', $.proxy(function (startPositionName, event) {
                var $timerAll = $('.dtp-body-time-content-seletor-all', event.currentTarget).eq(0),
                    transform = getTransformMatrix($timerAll),
                    vertical = transform.f,
                    itemHeight = Math.round($timerAll.outerHeight() / $timerAll.children().size()),
                    count = Math.round(vertical / itemHeight),
                    data = {target: event.currentTarget},
                    inertia = event.gesture.velocityY * ('up' === event.gesture.direction ? 1 : -1),
                    $item,
                    type,
                    value;

                count = Math.round(count + (count * inertia * this.options.inertiaVelocity));

                if (count > 0) {
                    count = 0;

                } else if (count < (-$timerAll.children().size() + 1)) {
                    count = -$timerAll.children().size() + 1;
                }

                vertical = itemHeight * count;

                $timerAll.css('-webkit-transition', '');
                $timerAll.css('transition', '');
                $timerAll.css('-webkit-transform', 'translate3d(0px, ' + vertical + 'px, 0px)');
                $timerAll.css('transform', 'translate3d(0px, ' + vertical + 'px, 0px)');

                delete this[startPositionName];
                $(event.currentTarget).removeClass('dtp-timer-on-drag');

                $item = $($timerAll.children().get(-count));
                type = $item.attr('data-time-type');
                value = $item.attr('data-time-value');

                switch (type) {
                case 'hour':
                    if (this.currentDate.hours() > 11 && this.options.format.indexOf('H') < 0) {
                        this.setHour(parseInt(value, 10) + 12);

                    } else {
                        this.setHour(parseInt(value, 10));
                    }
                    break;
                case 'minute':
                    this.setMinute(parseInt(value, 10));
                    break;
                case 'second':
                    this.setSecond(parseInt(value, 10));
                    break;
                case 'meridiem':
                    this.setMeridiem(value);
                    break;
                default:
                    break;
                }
            }, self, startPositionName));
    }

    /**
     * Init the timer hammer instance.
     *
     * @param {DatetimePicker} self The datetime picker instance
     *
     * @private
     */
    function initTimerSwipe(self) {
        dragTimerAction(self, 'hours');
        dragTimerAction(self, 'minutes');
        dragTimerAction(self, 'seconds');
        dragTimerAction(self, 'meridiem');
    }

    /**
     * Destroy the timmer hammer instance.
     *
     * @param {DatetimePicker} self The datetime picker instance
     *
     * @private
     */
    function destroyTimerSwipe(self) {
        if (!Hammer) {
            return;
        }
        var hours = 'hammerTimerHours',
            minutes = 'hammerTimerMinutes',
            seconds = 'hammerTimerSeconds',
            meridiem = 'hammerTimerMeridiem';

        delete self[hours];
        delete self[minutes];
        delete self[seconds];
        delete self[meridiem];
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

        type = type.charAt(0).toUpperCase() + type.slice(1);

        if (delta > 0) {
            self['previous' + type]();

        } else {
            self['next' + type]();
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
     * @this DatetimePicker
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
     * @this DatetimePicker
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
     * Generate the timer picker.
     *
     * @param {DatetimePicker} self
     *
     * @this DatetimePicker
     * @private
     */
    function generateTimer(self) {
        var format,
            hourSize,
            hourFormat,
            minuteFormat,
            secondFormat,
            $wrapper,
            $hours,
            $minutes,
            $seconds,
            $meridiem,
            i,
            j,
            k;

        format = self.options.format;
        hourSize = format.indexOf('H') < 0 ? 12 : 24;
        hourFormat = format.indexOf('HH') >= 0 ? 'HH' : 'H';
        hourFormat = format.indexOf('hh') >= 0 ? 'hh' : hourFormat;
        hourFormat = format.indexOf('h') >= 0 ? 'h' : hourFormat;
        minuteFormat = format.indexOf('mm') >= 0 ? 'mm' : 'm';
        secondFormat = format.indexOf('ss') >= 0 ? 'ss' : 's';
        $wrapper = $('.dtp-body-time-wrapper', self.$picker);
        $hours = $('.dtp-body-time-content-hours .dtp-body-time-content-seletor-all', self.$picker);
        $minutes = $('.dtp-body-time-content-minutes .dtp-body-time-content-seletor-all', $wrapper);
        $seconds = $('.dtp-body-time-content-seconds .dtp-body-time-content-seletor-all', $wrapper);
        $meridiem = $('.dtp-body-time-content-meridiem .dtp-body-time-content-seletor-all', $wrapper);

        if (self.options.withMinutes) {
            $wrapper.addClass('time-has-minutes');
        }

        if (self.options.withSeconds) {
            $wrapper.addClass('time-has-seconds');
        }

        if (format.indexOf('H') < 0) {
            $wrapper.addClass('time-has-meridiem');
        }

        // hours
        for (i = 0; i < hourSize; i += 1) {
            $hours.append('<span data-time-type="hour" data-time-value="' + i + '">' + moment({hour: i}).lang(self.options.locale).format(hourFormat) + '</span>');
        }

        // minutes
        for (j = 0; j < 60; j += 1) {
            $minutes.append('<span data-time-type="minute" data-time-value="' + j + '">' + moment({minute: j}).lang(self.options.locale).format(minuteFormat) + '</span>');
        }

        // seconds
        for (k = 0; k < 60; k += 1) {
            $seconds.append('<span data-time-type="second" data-time-value="' + k + '">' + moment({second: k}).lang(self.options.locale).format(secondFormat) + '</span>');
        }

        // meridiem
        $meridiem.append('<span data-time-type="meridiem" data-time-value="am">' + self.currentDate.lang().meridiem(1, 0, false) + '</span>');
        $meridiem.append('<span data-time-type="meridiem" data-time-value="pm">' + self.currentDate.lang().meridiem(23, 0, false) + '</span>');
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
        inertiaVelocity:   0.07
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
            hours:   'Hours',
            minutes: 'Minutes',
            seconds: 'Seconds',
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
            format,
            $timeAllWrappers;

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
            '<div class="dtp-body-time-wrapper">',
            '<div class="dtp-body-time-header">',
            '<div class="dtp-body-time-title-hours">',
            '<span>' + this.langData().hours + '</span>',
            '</div>',
            '<div class="dtp-body-time-title-minutes">',
            '<span>' + this.langData().minutes + '</span>',
            '</div>',
            '<div class="dtp-body-time-title-seconds">',
            '<span>' + this.langData().seconds + '</span>',
            '</div>',
            '<div class="dtp-body-time-title-meridiem">',
            '</div>',
            '</div>',
            '<div class="dtp-body-time-content">',
            '<div class="dtp-body-time-content-hours">',
            '<span class="dtp-time-btn dtp-time-hour-btn-next"></span>',
            '<span class="dtp-time-btn dtp-time-hour-btn-previous"></span>',
            '<div class="dtp-body-time-content-seletor">',
            '<div class="dtp-body-time-content-seletor-all">',
            '</div>',
            '</div>',
            '</div>',
            '<div class="dtp-body-time-content-minutes">',
            '<span class="dtp-time-btn dtp-time-minute-btn-next"></span>',
            '<span class="dtp-time-btn dtp-time-minute-btn-previous"></span>',
            '<div class="dtp-body-time-content-seletor">',
            '<div class="dtp-body-time-content-seletor-all">',
            '</div>',
            '</div>',
            '</div>',
            '<div class="dtp-body-time-content-seconds">',
            '<span class="dtp-time-btn dtp-time-second-btn-next"></span>',
            '<span class="dtp-time-btn dtp-time-second-btn-previous"></span>',
            '<div class="dtp-body-time-content-seletor">',
            '<div class="dtp-body-time-content-seletor-all">',
            '</div>',
            '</div>',
            '</div>',
            '<div class="dtp-body-time-content-meridiem">',
            '<span class="dtp-time-btn dtp-time-meridiem-btn-next"></span>',
            '<span class="dtp-time-btn dtp-time-meridiem-btn-previous"></span>',
            '<div class="dtp-body-time-content-seletor">',
            '<div class="dtp-body-time-content-seletor-all">',
            '</div>',
            '</div>',
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

        $timeAllWrappers = $('.dtp-body-time-content-seletor-all', this.$picker);

        // disabled transition
        $timeAllWrappers.css('-webkit-transition', 'none');
        $timeAllWrappers.css('transition', 'none');

        this.refreshValue();
        this.position();

        // restore transition
        $timeAllWrappers.css('-webkit-transition', '');
        $timeAllWrappers.css('transition', '');

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
        this.$picker.on(this.eventType, 'span.dtp-time-hour-btn-next', $.proxy(DatetimePicker.prototype.nextHour, this));
        this.$picker.on(this.eventType, 'span.dtp-time-hour-btn-previous', $.proxy(DatetimePicker.prototype.previousHour, this));
        this.$picker.on(this.eventType, 'span.dtp-time-minute-btn-next', $.proxy(DatetimePicker.prototype.nextMinute, this));
        this.$picker.on(this.eventType, 'span.dtp-time-minute-btn-previous', $.proxy(DatetimePicker.prototype.previousMinute, this));
        this.$picker.on(this.eventType, 'span.dtp-time-second-btn-next', $.proxy(DatetimePicker.prototype.nextSecond, this));
        this.$picker.on(this.eventType, 'span.dtp-time-second-btn-previous', $.proxy(DatetimePicker.prototype.previousSecond, this));
        this.$picker.on(this.eventType, 'span.dtp-time-meridiem-btn-next', $.proxy(DatetimePicker.prototype.toggleMeridiem, this));
        this.$picker.on(this.eventType, 'span.dtp-time-meridiem-btn-previous', $.proxy(DatetimePicker.prototype.toggleMeridiem, this));
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-year', null, this, scrollYear);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-month', null, this, scrollMonth);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-calendar-wrapper', null, this, scrollMonth);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-content-hours', null, this, scrollHour);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-content-minutes', null, this, scrollMinute);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-content-seconds', null, this, scrollSecond);
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-content-meridiem', null, this, scrollMeridiem);
        $(document).on(this.eventType + '.st.datetimepicker' + this.guid, null, this, closeExternal);
        $(window).on('resize.st.datetimepicker' + this.guid, null, this, closeExternal);
        $(window).on('keyup.st.datetimepicker' + this.guid, null, this, keyboardAction);
        $(window).on('scroll.st.datetimepicker' + this.guid, null, this, closeExternal);

        initCalendarSwipe(this);
        initTimerSwipe(this);
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
        this.$picker.off(this.eventType, 'span.dtp-time-hour-btn-next', $.proxy(DatetimePicker.prototype.nextHour, this));
        this.$picker.off(this.eventType, 'span.dtp-time-hour-btn-previous', $.proxy(DatetimePicker.prototype.previousHour, this));
        this.$picker.off(this.eventType, 'span.dtp-time-minute-btn-next', $.proxy(DatetimePicker.prototype.nextMinute, this));
        this.$picker.off(this.eventType, 'span.dtp-time-minute-btn-previous', $.proxy(DatetimePicker.prototype.previousMinute, this));
        this.$picker.off(this.eventType, 'span.dtp-time-second-btn-next', $.proxy(DatetimePicker.prototype.nextSecond, this));
        this.$picker.off(this.eventType, 'span.dtp-time-second-btn-previous', $.proxy(DatetimePicker.prototype.previousSecond, this));
        this.$picker.off(this.eventType, 'span.dtp-time-meridiem-btn-next', $.proxy(DatetimePicker.prototype.toggleMeridiem, this));
        this.$picker.off(this.eventType, 'span.dtp-time-meridiem-btn-previous', $.proxy(DatetimePicker.prototype.toggleMeridiem, this));
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-year', null, this, scrollYear);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-month', null, this, scrollMonth);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-calendar-wrapper', null, this, scrollMonth);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-content-hours', null, this, scrollHour);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-content-minutes', null, this, scrollMinute);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-content-seconds', null, this, scrollSecond);
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-content-meridiem', null, this, scrollMeridiem);
        this.$picker.remove();
        this.$picker = null;
        destroyCalendarSwipe(this);
        destroyTimerSwipe(this);
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
     * @this DatetimePicker
     */
    DatetimePicker.prototype.refreshTimePicker = function () {
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        var $header = this.$picker.children('.' + this.options.classHeaderPicker).eq(0),
            $title = $header.children('.' + this.options.classHeaderPicker + '-title').eq(0),
            $body = this.$picker.children('.' + this.options.classBodyPicker),
            // time
            $timeWrapper = $('.dtp-body-time-wrapper', $body),
            $hourAll = $('.dtp-body-time-content-hours .dtp-body-time-content-seletor-all', $timeWrapper),
            $minuteAll = $('.dtp-body-time-content-minutes .dtp-body-time-content-seletor-all', $timeWrapper),
            $secondAll = $('.dtp-body-time-content-seconds .dtp-body-time-content-seletor-all', $timeWrapper),
            $meridiemAll = $('.dtp-body-time-content-meridiem .dtp-body-time-content-seletor-all', $timeWrapper),
            itemHeight = -($('span[data-time-type=hour]', $timeWrapper).outerHeight()),
            hours,
            meridiem;

        // title
        $title.text(this.currentDate.format(this.options.format));

        // hour list
        hours = this.options.format.indexOf('H') < 0 ? (this.currentDate.hours() % 12 || 0) : this.currentDate.hours();

        $hourAll.css('-webkit-transform', 'translate3d(0px, ' + hours * itemHeight + 'px, 0px)');
        $hourAll.css('transform', 'translate3d(0px, ' + hours * itemHeight + 'px, 0px)');

        // minute list
        $minuteAll.css('-webkit-transform', 'translate3d(0px, ' + this.currentDate.minutes() * itemHeight + 'px, 0px)');
        $minuteAll.css('transform', 'translate3d(0px, ' + this.currentDate.minutes() * itemHeight + 'px, 0px)');

        // second list
        $secondAll.css('-webkit-transform', 'translate3d(0px, ' + this.currentDate.seconds() * itemHeight + 'px, 0px)');
        $secondAll.css('transform', 'translate3d(0px, ' + this.currentDate.seconds() * itemHeight + 'px, 0px)');

        // meridiem list
        meridiem = this.currentDate.hours() > 11 ? 1 : 0;

        $meridiemAll.css('-webkit-transform', 'translate3d(0px, ' + meridiem * itemHeight + 'px, 0px)');
        $meridiemAll.css('transform', 'translate3d(0px, ' + meridiem * itemHeight + 'px, 0px)');
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

        var $timeAllWrappers = $('.dtp-body-time-content-seletor-all', this.$picker);

        // disabled transition
        $timeAllWrappers.css('-webkit-transition', 'none');
        $timeAllWrappers.css('transition', 'none');

        this.$picker.attr('data-tab-selected', 'time');
        this.refreshTimePicker();
        this.position();

        // restore transition
        $timeAllWrappers.css('-webkit-transition', '');
        $timeAllWrappers.css('transition', '');
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

        this.currentDate.add('hour', -1);
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

        this.currentDate.add('hour', 1);
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

        this.currentDate.add('minute', -1);
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

        this.currentDate.add('minute', 1);
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

        this.currentDate.add('second', -1);
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

        this.currentDate.add('second', 1);
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
            this.currentDate.add('hour', 12);

        } else if (this.currentDate.hours() < 12 && 'pm' === meridiem) {
            this.currentDate.add('hour', -12);

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
        if (null === this.currentDate || null === this.$picker) {
            return;
        }

        if (this.currentDate.hours() > 11) {
            this.currentDate.add('hour', -12);

        } else {
            this.currentDate.add('hour', 12);
        }

        this.refreshTimePicker();
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
