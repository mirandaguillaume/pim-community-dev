// Unit test for the `product_completeness-filter-react` bridge: `SelectFilterReact.extend({})`.
// Mock the React select bridge base (backboneExtend, same shape as datetime-filter-react.unit.tsx)
// BEFORE importing, so the bridge's inherited machinery is stubbed.

jest.mock(
  'oro/datafilter/select-filter-react',
  () => {
    function SelectFilterReact(this: any) {
      this.inputClass = 'AknTextField';
    }
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
    (SelectFilterReact as any).extend = backboneExtend;
    return SelectFilterReact;
  },
  {virtual: true}
);

import SelectFilterReact from 'oro/datafilter/select-filter-react';
import Bridge from '../../../Resources/public/js/datafilter/filter/product_completeness-filter-react';

// The whole point of this bridge is WHICH parent it extends: the legacy filter extended the
// Backbone `select-filter`, this one must extend the React `select-filter-react`. Re-pointing it
// back at the legacy base — the one mistake that would silently undo the migration — fails here.
test('extends the React select bridge, not the legacy Backbone one', () => {
  const filter: any = new Bridge();

  expect(filter instanceof (SelectFilterReact as any)).toBe(true);
});

// A bare extend must stay bare: the legacy `product_completeness-filter.js` is
// `SelectFilter.extend({})` and adds no behaviour of its own. Anything accumulating here is a
// divergence from the select filter that the grid metadata does not ask for.
test('adds no behaviour of its own, inheriting the select render whole', () => {
  const filter: any = new Bridge();

  expect(filter.inputClass).toBe('AknTextField');
  expect(Object.keys(Object.getPrototypeOf(filter))).toEqual([]);
});
