import 'jquery';
import 'underscore';
import DateFilter from 'oro/datafilter/date-filter';
import DateContext from 'pim/date-context';

export default DateFilter.extend({
  /**
   * CSS class for visual datetime input elements
   *
   * @property
   */
  inputClass: 'AknTextField',

  /**
   * Date widget options
   *
   * @property
   */
  datetimepickerOptions: {
    format: DateContext.get('time').format,
    defaultFormat: DateContext.get('time').defaultFormat,
    language: DateContext.get('language'),
    pickTime: true,
    pickSeconds: false,
    pick12HourFormat: DateContext.get('12_hour_format'),
  },
});
