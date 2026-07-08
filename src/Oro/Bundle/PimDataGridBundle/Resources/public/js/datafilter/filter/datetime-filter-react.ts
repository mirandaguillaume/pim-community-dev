import DateFilterReact from 'oro/datafilter/date-filter-react';
import DateContext from 'pim/date-context';

/**
 * React inner-render of the datetime datagrid filter (C1 Wave 5).
 *
 * Extends the React `date-filter-react` bridge — inheriting the ENTIRE React render (render,
 * `_renderReact`, `DateFilterCriteria` with its memoized Datepicker subtree + init/destroy lifecycle,
 * operator handling, value shape) — and overrides ONLY the picker options, exactly as the legacy
 * `datetime-filter.js` did on top of `date-filter.js`. The markup is identical (same `DateFilterCriteria`);
 * only `datetimepickerOptions` differ (time format + `pickTime`), and those flow through `_renderReact`
 * into the component's `Datepicker.init`.
 *
 * Added ALONGSIDE `datetime-filter.js`; only the `datetime` FilterTypeRegistry alias is re-pointed.
 */
export default DateFilterReact.extend({
  inputClass: 'AknTextField',
  datetimepickerOptions: {
    format: DateContext.get('time').format,
    defaultFormat: DateContext.get('time').defaultFormat,
    language: DateContext.get('language'),
    pickTime: true,
    pickSeconds: false,
    pick12HourFormat: DateContext.get('12_hour_format'),
  },
});
