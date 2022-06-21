import $ from 'jquery';
import '../../utils/cron-ui';
import { translations } from 'lev-config';

export default class CronField {
    constructor() {
        this.items = $();

        $('[data-lev-field="cron"]').each((index, cron) => this.addCron(cron));
        $('body').on('mutation._lev', this._onAddedNodes.bind(this));
    }

    addCron(cron) {
        cron = $(cron);
        this.items = this.items.add(cron);

        cron.find('.cron-selector').each((index, container) => {
            container = $(container);
            const input = container.closest('[data-lev-field]').find('input');

            container.jqCron({
                numeric_zero_pad: true,
                enabled_minute: true,
                multiple_dom: true,
                multiple_month: true,
                multiple_mins: true,
                multiple_dow: true,
                multiple_time_hours: true,
                multiple_time_minutes: true,
                default_period: 'hour',
                default_value: input.val() || '* * * * *',
                no_reset_button: false,
                bind_to: input,
                bind_method: {
                    set: function($element, value) {
                        $element.val(value);
                    }
                },
                texts: {
                    en: {
                        empty: translations.LEV_CORE['CRON.EVERY'],
                        empty_minutes: translations.LEV_CORE['CRON.EVERY'],
                        empty_time_hours: translations.LEV_CORE['CRON.EVERY_HOUR'],
                        empty_time_minutes: translations.LEV_CORE['CRON.EVERY_MINUTE'],
                        empty_day_of_week: translations.LEV_CORE['CRON.EVERY_DAY_OF_WEEK'],
                        empty_day_of_month: translations.LEV_CORE['CRON.EVERY_DAY_OF_MONTH'],
                        empty_month: translations.LEV_CORE['CRON.EVERY_MONTH'],
                        name_minute: translations.LEV_CORE['NICETIME.MINUTE'],
                        name_hour: translations.LEV_CORE['NICETIME.HOUR'],
                        name_day: translations.LEV_CORE['NICETIME.DAY'],
                        name_week: translations.LEV_CORE['NICETIME.WEEK'],
                        name_month: translations.LEV_CORE['NICETIME.MONTH'],
                        name_year: translations.LEV_CORE['NICETIME.YEAR'],
                        text_period: translations.LEV_CORE['CRON.TEXT_PERIOD'],
                        text_mins: translations.LEV_CORE['CRON.TEXT_MINS'],
                        text_time: translations.LEV_CORE['CRON.TEXT_TIME'],
                        text_dow: translations.LEV_CORE['CRON.TEXT_DOW'],
                        text_month: translations.LEV_CORE['CRON.TEXT_MONTH'],
                        text_dom: translations.LEV_CORE['CRON.TEXT_DOM'],
                        error1: translations.LEV_CORE['CRON.ERROR1'],
                        error2: translations.LEV_CORE['CRON.ERROR2'],
                        error3: translations.LEV_CORE['CRON.ERROR3'],
                        error4: translations.LEV_CORE['CRON.ERROR4'],
                        weekdays: translations.LEV_CORE['DAYS_OF_THE_WEEK'],
                        months: translations.LEV_CORE['MONTHS_OF_THE_YEAR']
                    }
                }
            });
        });
    }

    _onAddedNodes(event, target/* , record, instance */) {
        let crons = $(target).find('[data-lev-field="cron"]');
        if (!crons.length) { return; }

        crons.each((index, list) => {
            list = $(list);
            if (!~this.items.index(list)) {
                this.addCron(list);
            }
        });
    }
}

export let Instance = new CronField();
