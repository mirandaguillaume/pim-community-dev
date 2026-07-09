// Unit test for the `datetime-filter-react` bridge: `export default DateFilterReact.extend({...time
// picker options})`. Mock the React date bridge base (backboneExtend, from react-filter-base.unit.tsx)
// and pim/date-context (read at module-eval time to build datetimepickerOptions) BEFORE importing.

jest.mock(
  'oro/datafilter/date-filter-react',
  () => {
    function DateFilterReact(this: any) {}
    function backboneExtend(this: any, protoOverrides: any) {
      const Parent = this;
      function Sub(this: any) {
        Parent.apply(this, arguments);
      }
      Sub.prototype = Object.create(Parent.prototype);
      Object.assign(Sub.prototype, protoOverrides);
      (Sub as any).extend = backboneExtend;
      return Sub;
    }
    (DateFilterReact as any).extend = backboneExtend;
    return DateFilterReact;
  },
  {virtual: true}
);

jest.mock(
  'pim/date-context',
  () => ({
    __esModule: true,
    default: {
      get: (key: string) => {
        switch (key) {
          case 'time':
            return {format: 'HH:mm', defaultFormat: 'HH:mm:ss'};
          case 'language':
            return 'fr';
          case '12_hour_format':
            return false;
          default:
            return undefined;
        }
      },
    },
  }),
  {virtual: true}
);

import DateFilterReact from 'oro/datafilter/date-filter-react';
import Bridge from '../../../Resources/public/js/datafilter/filter/datetime-filter-react';

test('extends the React date bridge (inherits its render + Datepicker shield)', () => {
  const filter: any = new Bridge();

  expect(filter instanceof (DateFilterReact as any)).toBe(true);
});

test('overrides the picker options with the datetime (time format + pickTime) config', () => {
  const filter: any = new Bridge();

  expect(filter.inputClass).toBe('AknTextField');
  expect(filter.datetimepickerOptions.format).toBe('HH:mm');
  expect(filter.datetimepickerOptions.defaultFormat).toBe('HH:mm:ss');
  expect(filter.datetimepickerOptions.language).toBe('fr');
  expect(filter.datetimepickerOptions.pickTime).toBe(true);
  expect(filter.datetimepickerOptions.pickSeconds).toBe(false);
  expect(filter.datetimepickerOptions.pick12HourFormat).toBe(false);
});
