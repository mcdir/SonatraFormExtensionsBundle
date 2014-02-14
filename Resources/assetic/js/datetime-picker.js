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

    var DatetimePicker = function (element, options) {
        this.guid        = jQuery.guid;
        this.options     = $.extend({}, DatetimePicker.DEFAULTS, options);
        this.$element    = $(element);
        this.eventType   = mobileCheck() ? 'touchstart' : 'click';
        this.currentDate = null;
        this.$picker     = null;

        if (null != this.options.buttonId) {
            $('#' + this.options.buttonId).on('click' + '.st.datetimepicker', $.proxy(DatetimePicker.prototype.toggle, this));
        }

        if (this.options.openFocus) {
            this.$element.on(this.eventType + '.st.datetimepicker', $.proxy(DatetimePicker.prototype.toggle, this));
        }

        this.$element.on( 'keyup.st.datetimepicker', $.proxy(keyboardAction, this));
        this.$element.attr('autocomplete', 'off');
    };

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
        dragDistance:      70
    };

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

    DatetimePicker.prototype.enabled = function () {
        this.$element.attr('disabled', 'disabled');
    };

    DatetimePicker.prototype.disabled = function () {
        this.$element.removeAttr('disabled');
    };

    DatetimePicker.prototype.isDisabled = function () {
        return null != this.$element.attr('disabled');
    };

    DatetimePicker.prototype.isOpen = function () {
        return this.$element.hasClass(this.options.classOpen);
    };

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

    DatetimePicker.prototype.open = function () {
        if (this.isOpen() || this.isDisabled()) {
            return;
        }

        var tabSelected = this.options.datePicker ? 'date' : 'time';

        if (this.options.datePicker && this.options.timePickerFirst) {
            tabSelected = 'time';
        }

        this.$picker = $([
            '<div class="' + this.options.classWrapper + '" data-target="' + this.$element.attr('id') + '" data-tab-selected="' + tabSelected + '">',
            '<div class="' + this.options.classHeaderPicker + '">',
                '<a class="' + this.options.classHeaderPicker + '-title" href="#"></a>',
                '<div class="' + this.options.classHeaderPicker + '-tabs">',
                    '<ul class="' + this.options.classHeaderPicker + '-nav-tabs">',
                        '<li role="tab" class="' + this.options.classHeaderPicker + '-nav-tab dtp-tab-date">',
                            '<a href="#" role="button" tabindex="-1">' + this.langData().date + '</a>',
                        '</li>',
                        '<li role="tab" class="' + this.options.classHeaderPicker + '-nav-tab dtp-tab-time">',
                            '<a href="#" role="button" tabindex="-1">' + this.langData().time + '</a>',
                        '</li>',
                    '</ul>',
                '</div>',
            '</div>',
            '<div class="' + this.options.classBodyPicker + '">',
                '<div class="dtp-body-date">',
                    '<div class="dtp-body-header">',
                        '<div class="dtp-body-header-choice dtp-choice-month">',
                            '<div class="dtp-body-header-choice-content">',
                                '<a class="dtp-choice-btn dtp-choice-month-btn-prev" href="#" role="button" tabindex="-1"></a>',
                                '<div class="dtp-choice-value">',
                                    '<select class="dtp-choice-value-select dtp-choice-month-value">',
                                    '</select>',
                                '</div>',
                                '<a class="dtp-choice-btn dtp-choice-month-btn-next" href="#" role="button" tabindex="-1"></a>',
                            '</div>',
                        '</div>',
                        '<div class="dtp-body-header-choice dtp-choice-year">',
                            '<div class="dtp-body-header-choice-content">',
                                '<a class="dtp-choice-btn dtp-choice-year-btn-prev" href="#" role="button" tabindex="-1"></a>',
                                '<div class="dtp-choice-value">',
                                    '<select class="dtp-choice-value-select dtp-choice-year-value">',
                                    '</select>',
                                '</div>',
                                '<a class="dtp-choice-btn dtp-choice-year-btn-next" href="#" role="button" tabindex="-1"></a>',
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
                                '<a class="dtp-time-btn dtp-time-hour-btn-next" href="#" role="button" tabindex="-1"></a>',
                                '<a class="dtp-time-btn dtp-time-hour-btn-previous" href="#" role="button" tabindex="-1"></a>',
                                '<div class="dtp-body-time-content-seletor">',
                                    '<div class="dtp-body-time-content-seletor-all">',
                                    '</div>',
                                '</div>',
                            '</div>',
                            '<div class="dtp-body-time-content-minutes">',
                                '<a class="dtp-time-btn dtp-time-minute-btn-next" href="#" role="button" tabindex="-1"></a>',
                                '<a class="dtp-time-btn dtp-time-minute-btn-previous" href="#" role="button" tabindex="-1"></a>',
                                '<div class="dtp-body-time-content-seletor">',
                                    '<div class="dtp-body-time-content-seletor-all">',
                                    '</div>',
                                '</div>',
                            '</div>',
                            '<div class="dtp-body-time-content-seconds">',
                                '<a class="dtp-time-btn dtp-time-second-btn-next" href="#" role="button" tabindex="-1"></a>',
                                '<a class="dtp-time-btn dtp-time-second-btn-previous" href="#" role="button" tabindex="-1"></a>',
                                '<div class="dtp-body-time-content-seletor">',
                                    '<div class="dtp-body-time-content-seletor-all">',
                                    '</div>',
                                '</div>',
                            '</div>',
                            '<div class="dtp-body-time-content-meridiem">',
                                '<a class="dtp-time-btn dtp-time-meridiem-btn-next" href="#" role="button" tabindex="-1"></a>',
                                '<a class="dtp-time-btn dtp-time-meridiem-btn-previous" href="#" role="button" tabindex="-1"></a>',
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
                '<span class="' + this.options.classFooterPicker + '-btn dtp-btn-cancel"><a href="#" role="button" tabindex="-1">' + this.langData().cancel + '</a></span>',
                '<span class="' + this.options.classFooterPicker + '-btn dtp-btn-clear"><a href="#" role="button" tabindex="-1">' + this.langData().clear + '</a></span>',
                '<span class="' + this.options.classFooterPicker + '-btn dtp-btn-define"><a href="#" role="button" tabindex="-1">' + this.langData().define + '</a></span>',
            '</div>',
            '</div>'
        ].join(''));

        this.$element.after(this.$picker);

        var value = this.getValue();
        var format = this.options.format;

        if ('' == value) {
            value = undefined;
            format = undefined;
        }

        this.currentDate = moment(value, format);
        this.currentDate.lang(this.options.locale);

        generateWeekdays(this);
        generateTimer(this);

        var $timeAllWrappers = $('.dtp-body-time-content-seletor-all', this.$picker);

        // disabled transition
        $timeAllWrappers.css('-webkit-transition', 'none');
        $timeAllWrappers.css('transition', 'none');

        this.refreshValue();
        this.position();

        // restore transition
        $timeAllWrappers.css('-webkit-transition', '');
        $timeAllWrappers.css('transition', '');

        this.$element.addClass(this.options.classOpen);

        this.$picker.on('DOMMouseScroll mousewheel', $.proxy(preventScroll, this));
        this.$picker.on(this.eventType, 'a.' + this.options.classHeaderPicker + '-title', $.proxy(DatetimePicker.prototype.setToday, this));
        this.$picker.on(this.eventType, '.dtp-btn-cancel', $.proxy(DatetimePicker.prototype.cancel, this));
        this.$picker.on(this.eventType, '.dtp-btn-clear', $.proxy(DatetimePicker.prototype.clearValue, this));
        this.$picker.on(this.eventType, '.dtp-btn-define', $.proxy(DatetimePicker.prototype.defineValue, this));
        this.$picker.on(this.eventType, '.dtp-tab-date > a', $.proxy(DatetimePicker.prototype.showDate, this));
        this.$picker.on(this.eventType, '.dtp-tab-time > a', $.proxy(DatetimePicker.prototype.showTime, this));
        this.$picker.on('change',       'select.dtp-choice-year-value', $.proxy(DatetimePicker.prototype.setYear, this));
        this.$picker.on(this.eventType, 'a.dtp-choice-year-btn-prev', $.proxy(DatetimePicker.prototype.previousYear, this));
        this.$picker.on(this.eventType, 'a.dtp-choice-year-btn-next', $.proxy(DatetimePicker.prototype.nextYear, this));
        this.$picker.on('change',       'select.dtp-choice-month-value', $.proxy(DatetimePicker.prototype.setMonth, this));
        this.$picker.on(this.eventType, 'a.dtp-choice-month-btn-prev', $.proxy(DatetimePicker.prototype.previousMonth, this));
        this.$picker.on(this.eventType, 'a.dtp-choice-month-btn-next', $.proxy(DatetimePicker.prototype.nextMonth, this));
        this.$picker.on(this.eventType, '.dtp-body-calendar-day > a', $.proxy(DatetimePicker.prototype.setDatetime, this));
        this.$picker.on(this.eventType, 'a.dtp-time-hour-btn-next', $.proxy(DatetimePicker.prototype.nextHour, this));
        this.$picker.on(this.eventType, 'a.dtp-time-hour-btn-previous', $.proxy(DatetimePicker.prototype.previousHour, this));
        this.$picker.on(this.eventType, 'a.dtp-time-minute-btn-next', $.proxy(DatetimePicker.prototype.nextMinute, this));
        this.$picker.on(this.eventType, 'a.dtp-time-minute-btn-previous', $.proxy(DatetimePicker.prototype.previousMinute, this));
        this.$picker.on(this.eventType, 'a.dtp-time-second-btn-next', $.proxy(DatetimePicker.prototype.nextSecond, this));
        this.$picker.on(this.eventType, 'a.dtp-time-second-btn-previous', $.proxy(DatetimePicker.prototype.previousSecond, this));
        this.$picker.on(this.eventType, 'a.dtp-time-meridiem-btn-next', $.proxy(DatetimePicker.prototype.toggleMeridiem, this));
        this.$picker.on(this.eventType, 'a.dtp-time-meridiem-btn-previous', $.proxy(DatetimePicker.prototype.toggleMeridiem, this));
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-year', $.proxy(scrollYear, this));
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-month', $.proxy(scrollMonth, this));
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-calendar-wrapper', $.proxy(scrollMonth, this));
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-content-hours', $.proxy(scrollHour, this));
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-content-minutes', $.proxy(scrollMinute, this));
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-content-seconds', $.proxy(scrollSecond, this));
        this.$picker.on('DOMMouseScroll mousewheel', '.dtp-body-time-content-meridiem', $.proxy(scrollMeridiem, this));
        $(document).on(this.eventType + '.st.datetimepicker' + this.guid, $.proxy(closeExternal, this));
        $(window).on('resize.st.datetimepicker' + this.guid, $.proxy(closeExternal, this));
        $(window).on('keyup.st.datetimepicker' + this.guid, $.proxy(keyboardAction, this));
        $(window).on('scroll.st.datetimepicker' + this.guid, $.proxy(closeExternal, this));

        $.proxy(initCalendarSwipe, this)();
        $.proxy(initTimerSwipe, this)();
    };

    DatetimePicker.prototype.close = function () {
        if (!this.isOpen() || null == this.$picker) {
            return;
        }

        this.currentDate = null;
        this.$picker.off('DOMMouseScroll mousewheel', $.proxy(preventScroll, this));
        this.$picker.off(this.eventType, 'a.' + this.options.classHeaderPicker + '-title', $.proxy(DatetimePicker.prototype.setToday, this));
        this.$picker.off(this.eventType, '.dtp-btn-cancel', $.proxy(DatetimePicker.prototype.cancel, this));
        this.$picker.off(this.eventType, '.dtp-btn-clear', $.proxy(DatetimePicker.prototype.clearValue, this));
        this.$picker.off(this.eventType, '.dtp-btn-define', $.proxy(DatetimePicker.prototype.defineValue, this));
        this.$picker.off(this.eventType, '.dtp-tab-date > a', $.proxy(DatetimePicker.prototype.showDate, this));
        this.$picker.off(this.eventType, '.dtp-tab-time > a', $.proxy(DatetimePicker.prototype.showTime, this));
        this.$picker.off('change',       'select.dtp-choice-year-value', $.proxy(DatetimePicker.prototype.setYear, this));
        this.$picker.off(this.eventType, 'a.dtp-choice-year-btn-prev', $.proxy(DatetimePicker.prototype.previousYear, this));
        this.$picker.off(this.eventType, 'a.dtp-choice-year-btn-next', $.proxy(DatetimePicker.prototype.nextYear, this));
        this.$picker.off('change',       'select.dtp-choice-month-value', $.proxy(DatetimePicker.prototype.setMonth, this));
        this.$picker.off(this.eventType, 'a.dtp-choice-month-btn-prev', $.proxy(DatetimePicker.prototype.previousMonth, this));
        this.$picker.off(this.eventType, 'a.dtp-choice-month-btn-next', $.proxy(DatetimePicker.prototype.nextMonth, this));
        this.$picker.off(this.eventType, '.dtp-body-calendar-day > a', $.proxy(DatetimePicker.prototype.setDatetime, this));
        this.$picker.off(this.eventType, 'a.dtp-time-hour-btn-next', $.proxy(DatetimePicker.prototype.nextHour, this));
        this.$picker.off(this.eventType, 'a.dtp-time-hour-btn-previous', $.proxy(DatetimePicker.prototype.previousHour, this));
        this.$picker.off(this.eventType, 'a.dtp-time-minute-btn-next', $.proxy(DatetimePicker.prototype.nextMinute, this));
        this.$picker.off(this.eventType, 'a.dtp-time-minute-btn-previous', $.proxy(DatetimePicker.prototype.previousMinute, this));
        this.$picker.off(this.eventType, 'a.dtp-time-second-btn-next', $.proxy(DatetimePicker.prototype.nextSecond, this));
        this.$picker.off(this.eventType, 'a.dtp-time-second-btn-previous', $.proxy(DatetimePicker.prototype.previousSecond, this));
        this.$picker.off(this.eventType, 'a.dtp-time-meridiem-btn-next', $.proxy(DatetimePicker.prototype.toggleMeridiem, this));
        this.$picker.off(this.eventType, 'a.dtp-time-meridiem-btn-previous', $.proxy(DatetimePicker.prototype.toggleMeridiem, this));
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-year', $.proxy(scrollYear, this));
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-header-choice.dtp-choice-month', $.proxy(scrollMonth, this));
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-calendar-wrapper', $.proxy(scrollMonth, this));
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-content-hours', $.proxy(scrollHour, this));
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-content-minutes', $.proxy(scrollMinute, this));
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-content-seconds', $.proxy(scrollSecond, this));
        this.$picker.off('DOMMouseScroll mousewheel', '.dtp-body-time-content-meridiem', $.proxy(scrollMeridiem, this));
        this.$picker.remove();
        this.$picker = null;
        $.proxy(destroyCalendarSwipe, this)();
        $.proxy(destroyTimerSwipe, this)();
        this.$element.removeClass(this.options.classOpen);

        $(document).off(this.eventType + '.st.datetimepicker' + this.guid, $.proxy(closeExternal, this));
        $(window).off('resize.st.datetimepicker' + this.guid, $.proxy(closeExternal, this));
        $(window).off('keyup.st.datetimepicker' + this.guid, $.proxy(keyboardAction, this));
        $(window).off('scroll.st.datetimepicker' + this.guid, $.proxy(closeExternal, this));
    };

    DatetimePicker.prototype.position = function () {
        if (null == this.$picker) {
            return;
        }

        var top = this.$element.offset()['top'] + this.$element.outerHeight();

        this.$picker.css('width', this.$element.width());
        this.$picker.css('left', this.$element.offset()['left']);

        if ((this.$picker.outerHeight() + top) > $(window).height()) {
            top = this.$element.offset()['top'] - this.$picker.outerHeight();
        }

        if (top - $(window).scrollTop() < 0) {
            top = this.$element.offset()['top'] + this.$element.outerHeight();
            top += $(window).height() - (top + this.$picker.outerHeight() - $(window).scrollTop());
        }

        this.$picker.css('top', top);
    }

    DatetimePicker.prototype.langData = function (locale) {
        if (undefined == locale) {
            locale = this.options.locale;
        }

        if (undefined == DatetimePicker.LANGUAGES[locale]) {
            locale = 'en';
        }

        return DatetimePicker.LANGUAGES[locale];
    };

    DatetimePicker.prototype.setValue = function (date) {
        if (typeof date == 'string') {
            date = moment(date, this.options.format);
            date.lang(this.options.locale);
        }

        if (null != date) {
            date = date.format(this.options.format);
        }

        this.$element.val(date);
    };

    DatetimePicker.prototype.getValue = function () {
        return this.$element.val();
    };

    DatetimePicker.prototype.refreshValue = function () {
        if (null == this.currentDate) {
            return;
        }

        var value = this.getValue();
        var format = this.options.format;

        if ('' == value) {
            value = undefined;
            format = undefined;
        }

        this.currentDate = moment(value, format);
        this.currentDate.lang(this.options.locale);

        this.refreshPicker();
    }

    DatetimePicker.prototype.refreshPicker = function () {
        this.refreshDatePicker();
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.refreshDatePicker = function () {
        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        var $header = this.$picker.children('.' + this.options.classHeaderPicker);
        var $title = $header.children('.' + this.options.classHeaderPicker + '-title');
        var $body = this.$picker.children('.' + this.options.classBodyPicker);

        // title
        $title.text(this.currentDate.format(this.options.format));

        // months list
        var $months = $('.dtp-choice-month-value', $body);
        var monthList = moment.langData()._monthsShort;
        $months.empty();

        for (var i = 0; i < monthList.length; i++) {
            var selected = i == this.currentDate.month() ? ' selected="selected"' : '';
            $months.append('<option value="' + i + '"' + selected + '>' + monthList[i] + '</option>');
        }

        // years list
        var $years = $('.dtp-choice-year-value', $body);
        var startYear = this.currentDate.clone();
        var endYear = this.currentDate.clone();

        $years.empty();
        startYear = startYear.add('year', -10).year();
        endYear = endYear.add('year', 10).year();

        for (var i = startYear; i <= endYear; i++) {
            var selected = i == this.currentDate.year() ? ' selected="selected"' : '';
            $years.append('<option value="' + i + '"' + selected + '>' + i + '</option>');
        }

        // calendar
        var $calendarWrapper = $('.dtp-body-calendar-wrapper', $body);
        $calendarWrapper.empty();
        $calendarWrapper.append(generateCalendars(this, this.currentDate));
    };

    DatetimePicker.prototype.refreshTimePicker = function () {
        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        var $header = this.$picker.children('.' + this.options.classHeaderPicker);
        var $title = $header.children('.' + this.options.classHeaderPicker + '-title');
        var $body = this.$picker.children('.' + this.options.classBodyPicker);

        // title
        $title.text(this.currentDate.format(this.options.format));

        // time
        var $timeWrapper = $('.dtp-body-time-wrapper', $body);
        var $timeAllWrappers = $('.dtp-body-time-content-seletor-all', $timeWrapper);
        var $hourAll = $('.dtp-body-time-content-hours .dtp-body-time-content-seletor-all', $timeWrapper);
        var $minuteAll = $('.dtp-body-time-content-minutes .dtp-body-time-content-seletor-all', $timeWrapper);
        var $secondAll = $('.dtp-body-time-content-seconds .dtp-body-time-content-seletor-all', $timeWrapper);
        var $meridiemAll = $('.dtp-body-time-content-meridiem .dtp-body-time-content-seletor-all', $timeWrapper);
        var itemHeight = - $('span[data-time-type=hour]', $timeWrapper).outerHeight();

        // hour list
        var hours = this.options.format.indexOf('H') < 0 ? (this.currentDate.hours() % 12 || 0) : this.currentDate.hours();

        $hourAll.css('-webkit-transform', 'translate3d(0px, ' + hours * itemHeight +'px, 0px)');
        $hourAll.css('transform', 'translate3d(0px, ' + hours * itemHeight +'px, 0px)');

        // minute list
        $minuteAll.css('-webkit-transform', 'translate3d(0px, ' + this.currentDate.minutes() * itemHeight +'px, 0px)');
        $minuteAll.css('transform', 'translate3d(0px, ' + this.currentDate.minutes() * itemHeight +'px, 0px)');

        // second list
        $secondAll.css('-webkit-transform', 'translate3d(0px, ' + this.currentDate.seconds() * itemHeight +'px, 0px)');
        $secondAll.css('transform', 'translate3d(0px, ' + this.currentDate.seconds() * itemHeight +'px, 0px)');

        // meridiem list
        var meridiem = this.currentDate.hours() > 11 ? 1 : 0;

        $meridiemAll.css('-webkit-transform', 'translate3d(0px, ' + meridiem * itemHeight +'px, 0px)');
        $meridiemAll.css('transform', 'translate3d(0px, ' + meridiem * itemHeight +'px, 0px)');
    };

    DatetimePicker.prototype.cancel = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        this.close();
    };

    DatetimePicker.prototype.clearValue = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.setValue(null);
        this.close();
    };

    DatetimePicker.prototype.defineValue = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null == this.currentDate || null == this.$picker || !this.currentDate.isValid()) {
            return;
        }

        this.setValue(this.currentDate);
        this.close();
    };

    DatetimePicker.prototype.showDate = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.datePicker || null == this.currentDate || null == this.$picker) {
            return;
        }

        this.$picker.attr('data-tab-selected', 'date');
        this.refreshDatePicker();
        this.position();
    };

    DatetimePicker.prototype.showTime = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.timePicker || null == this.$picker) {
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

    DatetimePicker.prototype.setDatetime = function (datetime) {
        if (datetime instanceof jQuery.Event) {
            event.preventDefault();
            event.stopPropagation();
            datetime = $(event.target).attr('data-date-value');
        }

        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        if (typeof datetime == 'string') {
            datetime = moment(datetime, this.options.format);
        }

        this.currentDate = datetime;
        this.currentDate.lang(this.options.locale);
        this.refreshPicker();
    }

    DatetimePicker.prototype.setToday = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        this.setDatetime(moment());
    }

    DatetimePicker.prototype.setYear = function (year) {
        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        if (year instanceof jQuery.Event) {
            year = $(event.target).val();
        }

        this.currentDate.year(year);
        this.refreshDatePicker();
    };

    DatetimePicker.prototype.previousYear = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('year', -1);
        this.refreshDatePicker();
    };

    DatetimePicker.prototype.nextYear = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('year', 1);
        this.refreshDatePicker();
    };

    DatetimePicker.prototype.setMonth = function (month) {
        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        if (month instanceof jQuery.Event) {
            month = $(event.target).val();
        }

        this.currentDate.month(parseInt(month));
        this.refreshDatePicker();
    };

    DatetimePicker.prototype.previousMonth = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('month', -1);
        this.refreshDatePicker();
    };

    DatetimePicker.prototype.nextMonth = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('month', 1);
        this.refreshDatePicker();
    };

    DatetimePicker.prototype.setHour = function (hour) {
        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.hour(hour);
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.previousHour = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('hour', -1);
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.nextHour = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('hour', 1);
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.setMinute = function (minute) {
        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.minute(minute);
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.previousMinute = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.withMinutes || null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('minute', -1);
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.nextMinute = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.withMinutes || null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('minute', 1);
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.setSecond = function (second) {
        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.second(second);
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.previousSecond = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.withSeconds || null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('second', -1);
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.nextSecond = function (event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!this.options.withSeconds || null == this.currentDate || null == this.$picker) {
            return;
        }

        this.currentDate.add('second', 1);
        this.refreshTimePicker();
    };

    DatetimePicker.prototype.setMeridiem = function (meridiem) {
        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        meridiem = meridiem.toLowerCase();

        if (this.currentDate.hours() >= 12 && 'am' == meridiem) {
            this.currentDate.add('hour', 12);

        } else if (this.currentDate.hours() < 12 && 'pm' == meridiem) {
            this.currentDate.add('hour', -12);

        } else {
            return;
        }

        this.refreshTimePicker();
    };

    DatetimePicker.prototype.toggleMeridiem = function () {
        if (null == this.currentDate || null == this.$picker) {
            return;
        }

        if (this.currentDate.hours() > 11) {
            this.currentDate.add('hour', -12);

        } else {
            this.currentDate.add('hour', 12);
        }

        this.refreshTimePicker();
    }

    DatetimePicker.prototype.destroy = function () {
        this.close();

        if (null != this.options.buttonId) {
            $('#' + this.options.buttonId).off('click' + '.st.datetimepicker', $.proxy(DatetimePicker.prototype.toggle, this));
        }

        if (this.options.openFocus) {
            this.$element.off(this.eventType + '.st.datetimepicker', $.proxy(DatetimePicker.prototype.toggle, this));
        }
    };

    function initCalendarSwipe () {
        if (!Hammer) {
            return;
        }

        this.hammerCalendar = new Hammer($('.dtp-body-calendar-wrapper', this.$picker).get(0), {
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
        }, this))

        .on('drag', $.proxy(function (event) {
            event.stopPropagation();
            event.preventDefault();

            var $calendarAll = $('.dtp-body-calendar-all', this.$picker);
            var $calendar = $('.dtp-body-calendar[data-calendar-name=current]', $calendarAll);
            var width = $calendar.outerWidth();
            var height = $calendar.outerHeight();
            var horizontal = 0;
            var vertical = 0;

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
            $calendarAll.css('-webkit-transform', 'translate3d(' + horizontal +'px, ' + vertical +'px, 0px)');
            $calendarAll.css('transform', 'translate3d(' + horizontal +'px, ' + vertical +'px, 0px)');
        }, this))

        .on('dragend', $.proxy(function (event) {
            var $calendarAll = $('.dtp-body-calendar-all', this.$picker);
            var $calendar = $('.dtp-body-calendar[data-calendar-name=current]', $calendarAll);
            var transform = getTransformMatrix($calendarAll);
            var horizontal = transform.e;
            var vertical = transform.f;
            var type = null;

            if (0 != horizontal && Math.abs(horizontal) >= Math.min($calendar.outerWidth() / 3, this.options.dragDistance)) {
                if (horizontal < 0) {
                    type = 'nextMonth';
                    horizontal = -Math.round($calendar.outerWidth());

                } else {
                    type = 'previousMonth';
                    horizontal = Math.round($calendar.outerWidth());
                }

            } else if (0 != vertical && Math.abs(vertical) >= Math.min($calendar.outerHeight() / 3, this.options.dragDistance)) {
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

            $calendarAll.on('transitionend webkitTransitionEnd oTransitionEnd', null, type, $.proxy(dragEndCalendarTransition, this));
            $calendarAll.css('-webkit-transition', '');
            $calendarAll.css('transition', '');
            $calendarAll.css('-webkit-transform', 'translate3d(' + horizontal +'px, ' + vertical +'px, 0px)');
            $calendarAll.css('transform', 'translate3d(' + horizontal +'px, ' + vertical +'px, 0px)');
            
        }, this));
    };

    function destroyCalendarSwipe () {
        if (!Hammer) {
            return;
        }

        delete this.hammerCalendar;
    };

    function dragEndCalendarTransition (event) {
        var $calendarAll = $('.dtp-body-calendar-all', this.$picker);

        $calendarAll.off('transitionend webkitTransitionEnd oTransitionEnd');
        $calendarAll.css('-webkit-transition', 'none');
        $calendarAll.css('transition', 'none');
        $calendarAll.css('-webkit-transform', '');
        $calendarAll.css('transform', '');

        switch (event.data) {
            case 'nextYear':
                this.nextYear(event);
                break;
            case 'previousYear':
                this.previousYear(event);
                break;
            case 'nextMonth':
                this.nextMonth(event);
                break;
            case 'previousMonth':
                this.previousMonth(event);
                break;
            default:
                break;
        }
    };

    function initTimerSwipe () {
        $.proxy(dragTimerAction, this, 'hours')();
        $.proxy(dragTimerAction, this, 'minutes')();
        $.proxy(dragTimerAction, this, 'seconds')();
        $.proxy(dragTimerAction, this, 'meridiem')();
    };

    function destroyTimerSwipe () {
        if (!Hammer) {
            return;
        }

        delete this['hammerTimerHours'];
        delete this['hammerTimerMinutes'];
        delete this['hammerTimerSeconds'];
        delete this['hammerTimerMeridiem'];
    };

    function dragTimerAction (type) {
        if (!Hammer) {
            return;
        }

        var name = 'hammerTimer' + type.charAt(0).toUpperCase() + type.slice(1);
        var startPositionName = 'hammerStartPosition' + type.charAt(0).toUpperCase() + type.slice(1);

        this[name] = new Hammer($('.dtp-body-time-content-' + type, this.$picker).get(0), {
            swipe: false,
            transform: false,
            prevent_default: true,
            drag_lock_to_axis: true
        })

        .on('dragstart', $.proxy(function (startPositionName, event) {
            var $timerSelector = $(event.currentTarget);
            var $timerAll = $('.dtp-body-time-content-seletor-all', $timerSelector);
            var name = type.charAt(0).toUpperCase() + type.slice(1);

            $timerSelector.addClass('dtp-timer-on-drag');
            this[startPositionName] = getTransformMatrix($timerAll);
        }, this, startPositionName))

        .on('drag', $.proxy(function (startPositionName, event) {
            var $timerAll = $('.dtp-body-time-content-seletor-all', event.currentTarget);
            var vertical = 0;
            var itemHeight = Math.round($timerAll.outerHeight() / $timerAll.children().size());

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
            $timerAll.css('-webkit-transform', 'translate3d(0px, ' + vertical +'px, 0px)');
            $timerAll.css('transform', 'translate3d(0px, ' + vertical +'px, 0px)');
        }, this, startPositionName))

        .on('dragend', $.proxy(function (startPositionName, event) {
            var $timerAll = $('.dtp-body-time-content-seletor-all', event.currentTarget);
            var transform = getTransformMatrix($timerAll);
            var vertical = transform.f;
            var itemHeight = Math.round($timerAll.outerHeight() / $timerAll.children().size());
            var count = Math.round(vertical / itemHeight);
            var data = {target: event.currentTarget};

            if (count > 0) {
                count = 0;

            } else if (count < (-$timerAll.children().size() + 1)) {
                count = -$timerAll.children().size() + 1;
            }

            vertical = itemHeight * count;

            $timerAll.css('-webkit-transition', '');
            $timerAll.css('transition', '');
            $timerAll.css('-webkit-transform', 'translate3d(0px, ' + vertical +'px, 0px)');
            $timerAll.css('transform', 'translate3d(0px, ' + vertical +'px, 0px)');

            delete this[startPositionName];
            $(event.currentTarget).removeClass('dtp-timer-on-drag');

            var $item = $($timerAll.children().get(-count));
            var type = $item.attr('data-time-type');
            var value = $item.attr('data-time-value');

            switch (type) {
                case 'hour':
                    if (this.currentDate.hours() > 11 && this.options.format.indexOf('H') < 0) {
                        this.setHour(parseInt(value) + 12);

                    } else {
                        this.setHour(parseInt(value));
                    }
                    break;
                case 'minute':
                    this.setMinute(parseInt(value));
                    break;
                case 'second':
                    this.setSecond(parseInt(value));
                    break;
                case 'meridiem':
                    this.setMeridiem(value);
                    break;
                default:
                    break;
            }
        }, this, startPositionName));
    };

    function getTransformMatrix ($dragger) {
        var transform = {e: 0, f: 0};

        if ($dragger.css('transform')) {
            if ('function' === typeof CSSMatrix) {
                transform = new CSSMatrix($dragger.css('transform'));

            } else if ('function' === typeof WebKitCSSMatrix) {
                transform = new WebKitCSSMatrix($dragger.css('transform'));

            } else if ('function' === typeof MSCSSMatrix) {
                transform = new MSCSSMatrix($dragger.css('transform'));

            } else {
                var reMatrix = /matrix\(\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*\,\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*\)/;
                var match = $dragger.css('transform').match(reMatrix);

                if (match) {
                    transform.e = parseInt(match[1]);
                    transform.f = parseInt(match[2]);
                }
            }
        }

        return transform;
    };

    function mobileCheck () {
        var check = false;

        (function (a) {
            if(/(android|ipad|playbook|silk|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) {
                check = true;
            }

        })(navigator.userAgent || navigator.vendor || window.opera);

        return check;
    };

    function keyboardAction (event) {
        if (!event instanceof jQuery.Event) {
            return;
        }

        if (event.keyCode  == 9) {// tab
            this.toggle(event);

        } else if (this.isOpen()) {// on opened picker
            if (event.keyCode == 27) {// escape
                this.close();

            } else if (event.keyCode == 13) {// enter
                this.defineValue();
                event.preventDefault();
                event.stopPropagation();

            } else {// refresh value
                this.refreshValue();
            }

        } else {// on closed picked
            if (event.keyCode == 40) {
                this.open();
            }
        }
    };

    function closeExternal (event) {
        var $target = $(event.currentTarget.activeElement);

        if ($target.hasClass(this.options.classOpen) || $(event.target).hasClass(this.options.classWrapper) || $(event.target).parents('.' + this.options.classWrapper).size() > 0) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        this.close();
    };

    function preventScroll (event) {
        var scrollTop = this.$picker.get(0).scrollTop;
        var scrollHeight = this.$picker.get(0).scrollHeight;
        var height = this.$picker.height();
        var delta = (event.type == 'DOMMouseScroll' ?
                event.originalEvent.detail * -40 :
                event.originalEvent.wheelDelta);
        var up = delta > 0;

        if (!up && -delta > scrollHeight - height - scrollTop) {
            this.$picker.scrollTop(scrollHeight);

            event.stopPropagation();
            event.preventDefault();
            event.returnValue = false;

            return false;

        } else if (up && delta > scrollTop) {
            this.$picker.scrollTop(0);

            event.stopPropagation();
            event.preventDefault();
            event.returnValue = false;

            return false;
        }
    };

    function scrollAction (event, type) {
        var delta = (event.type == 'DOMMouseScroll' ?
                event.originalEvent.detail * -40 :
                event.originalEvent.wheelDelta);

        type = type.charAt(0).toUpperCase() + type.slice(1);

        if (delta > 0) {
            this['previous' + type]();

        } else {
            this['next' + type]();
        }

        event.stopPropagation();
        event.preventDefault();
    }

    function scrollYear (event) {
        $.proxy(scrollAction, this, event, 'year')();
    };

    function scrollMonth (event) {
        $.proxy(scrollAction, this, event, 'month')();
    };

    function scrollHour (event) {
        $.proxy(scrollAction, this, event, 'hour')();
    };

    function scrollMinute (event) {
        $.proxy(scrollAction, this, event, 'minute')();
    };

    function scrollSecond (event) {
        $.proxy(scrollAction, this, event, 'second')();
    };

    function scrollMeridiem (event) {
        this.toggleMeridiem();

        event.stopPropagation();
        event.preventDefault();
    };

    function generateWeekdays (self) {
        var days = moment.langData(self.options.locale)._weekdaysMin.slice();
        var startDay = moment.langData(self.options.locale)._week.dow;
        var endDays = days.splice(0, startDay);
        var $days = $('.dtp-body-header-days', self.$picker);

        days = days.concat(endDays);
        $days.empty();

        for (var i = 0; i < days.length; i++) {
            $days.append('<div class="dtp-body-header-day" data-day-id="' + i + '">' + days[i] + '</div>');
        }
    };

    function generateCalendars (self, date) {
        var $calendars = $('<div class="dtp-body-calendar-all"></div');

        var $calendarCurrent = generateCalendar(self, 'current', date);
        $calendars.append($calendarCurrent);

        var $calendarPreviousMonth = generateCalendar(self, 'previous-month', date.clone().add('month', -1));
        $calendarPreviousMonth.css('-webkit-transform', 'translate3d(-100%, 0px, 0px)');
        $calendarPreviousMonth.css('transform', 'translate3d(-100%, 0px, 0px)');
        $calendars.append($calendarPreviousMonth);

        var $calendarNextMonth = generateCalendar(self, 'next-month', date.clone().add('month', 1));
        $calendarNextMonth.css('-webkit-transform', 'translate3d(100%, 0px, 0px)');
        $calendarNextMonth.css('transform', 'translate3d(100%, 0px, 0px)');
        $calendars.append($calendarNextMonth);

        var $calendarPreviousYear = generateCalendar(self, 'previous-year', date.clone().add('year', -1));
        $calendarPreviousYear.css('-webkit-transform', 'translate3d(0px, -100%, 0px)');
        $calendarPreviousYear.css('transform', 'translate3d(0px, -100%, 0px)');
        $calendars.append($calendarPreviousYear);

        var $calendarNextYear = generateCalendar(self, 'next-year', date.clone().add('year', 1));
        $calendarNextYear.css('-webkit-transform', 'translate3d(0px, 100%, 0px)');
        $calendarNextYear.css('transform', 'translate3d(0px, 100%, 0px)');
        $calendars.append($calendarNextYear);

        return $calendars;
    };

    function generateCalendar (self, name, date) {
        var today = moment();
            today.lang(self.options.locale);
        var startDay = date.clone().startOf('month');   
        var endDay = date.clone().endOf('month');
        var currentDay = startDay.clone();
        var $calendar = $('<div class="dtp-body-calendar" data-calendar-name="' + name + '"></div>');

        if (1 == currentDay.clone().startOf('week').dates()) {
            startDay.add('days', -7);
            currentDay.add('days', -7);
        }

        currentDay.startOf('week');
        currentDay.hours(date.hours());
        currentDay.minutes(date.minutes());
        currentDay.seconds(date.seconds());

        for (var i = 0; i < 6; i++) {
            var $week = $('<div class="dtp-body-calendar-week"></div>');

            for (var j = 0; j < 7; j++) {
                var number = currentDay.dates();
                var dayClass = 'dtp-body-calendar-day';

                if (currentDay.years() == date.years() && currentDay.months() == date.months()) {
                    if (number == date.dates()) {
                        dayClass += ' dtp-day-selected';
                    }

                    if (number == today.dates() && date.months() == today.months() && date.years() == today.years()) {
                        dayClass += ' dtp-day-today';
                    }

                } else {
                    dayClass += ' dtp-day-out';
                }

                var $day = $('<div class="' + dayClass + '"><div class="dtp-body-calendar-day-value" data-date-value="' + currentDay.format(self.options.format) + '">' + number + '</div></div>');

                $week.append($day);

                currentDay.add('days', 1);
            }

            $calendar.append($week);
        }

        return $calendar;
    };

    function generateTimer (self) {
        var format = self.options.format;
        var hourSize = format.indexOf('H') < 0 ? 12 : 24;
        var hourFormat = format.indexOf('HH') >= 0 ? 'HH' : 'H';
            hourFormat = format.indexOf('hh') >= 0 ? 'hh' : hourFormat;
            hourFormat = format.indexOf('h') >= 0 ? 'h' : hourFormat;
        var minuteFormat = format.indexOf('mm') >= 0 ? 'mm' : 'm';
        var secondFormat = format.indexOf('ss') >= 0 ? 'ss' : 's';
        var $wrapper = $('.dtp-body-time-wrapper', self.$picker);
        var $hours = $('.dtp-body-time-content-hours .dtp-body-time-content-seletor-all', self.$picker);
        var $minutes = $('.dtp-body-time-content-minutes .dtp-body-time-content-seletor-all', $wrapper);
        var $seconds = $('.dtp-body-time-content-seconds .dtp-body-time-content-seletor-all', $wrapper);
        var $meridiem = $('.dtp-body-time-content-meridiem .dtp-body-time-content-seletor-all', $wrapper);

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
        for (var i = 0; i < hourSize; i++) {
            $hours.append('<span data-time-type="hour" data-time-value="' + i + '">' + moment({hour: i}).lang(self.options.locale).format(hourFormat) + '</span>');
        }

        // minutes
        for (var i = 0; i < 60; i++) {
            $minutes.append('<span data-time-type="minute" data-time-value="' + i + '">' + moment({minute: i}).lang(self.options.locale).format(minuteFormat) + '</span>');
        }

        // seconds
        for (var i = 0; i < 60; i++) {
            $seconds.append('<span data-time-type="second" data-time-value="' + i + '">' + moment({second: i}).lang(self.options.locale).format(secondFormat) + '</span>');
        }

        // meridiem
        $meridiem.append('<span data-time-type="meridiem" data-time-value="am">' + self.currentDate.lang().meridiem(1, 0, false) + '</span>');
        $meridiem.append('<span data-time-type="meridiem" data-time-value="pm">' + self.currentDate.lang().meridiem(23, 0, false) + '</span>');
    };


    // DATETIME PICKER PLUGIN DEFINITION
    // =================================

    var old = $.fn.datetimepicker;

    $.fn.datetimepicker = function (option, _relatedTarget) {
        return this.each(function () {
            var $this   = $(this);
            var data    = $this.data('st.datetimepicker');
            var options = typeof option == 'object' && option;

            if (!data && option == 'destroy') {
                return;
            }

            if (!data) {
                $this.data('st.datetimepicker', (data = new DatetimePicker(this, options)));
            }

            if (typeof option == 'string') {
                data[option]();
            }
        });
    };

    $.fn.datetimepicker.Constructor = DatetimePicker;


    // DATETIME PICKER NO CONFLICT
    // ===========================

    $.fn.datetimepicker.noConflict = function () {
        $.fn.datetimepicker = old;

        return this;
    };


    // DATETIME PICKER DATA-API
    // ========================

    $(window).on('load', function () {
        $('[data-datetime-picker="true"]').each(function () {
            var $this = $(this);
            $this.datetimepicker($this.data());
        });
    });

}(jQuery);
