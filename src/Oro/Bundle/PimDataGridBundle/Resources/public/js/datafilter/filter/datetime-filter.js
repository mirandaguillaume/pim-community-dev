function __pimInterop(m) {
  return m && m.__esModule && 'default' in m ? m.default : m;
}

require('jquery');
require('underscore');
var DateFilter = __pimInterop(require('oro/datafilter/date-filter'));
var DateContext = __pimInterop(require('pim/date-context'));
('use strict');

module.exports = DateFilter.extend({
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
