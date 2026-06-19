// Mock the legacy AbstractFilter BEFORE importing ReactFilterBase (jest.mock is hoisted).
// `oro/datafilter/abstract-filter` (a raw Backbone.View) is not resolvable in the Jest/Stryker
// sandbox — provide a minimal Backbone-style constructor with the `.extend` chaining ReactFilterBase
// relies on, an `el`, and a `remove()` to chain to.
jest.mock(
  'oro/datafilter/abstract-filter',
  () => {
    function AbstractFilter(this: any) {
      this.el = document.createElement('div');
    }
    (AbstractFilter as any).prototype.remove = function (this: any) {
      this.removed = true;
      return this;
    };
    // Backbone-style extend: the subclass prototype chains off `this` (the CALLING class), not a
    // hardcoded AbstractFilter — otherwise ReactFilterBase.extend(...) would inherit AbstractFilter
    // directly and lose ReactFilterBase's render()/remove() (the bug CI caught: filter.render undefined).
    function backboneExtend(this: any, proto: any) {
      const Parent = this;
      function Sub(this: any) {
        Parent.apply(this, arguments);
      }
      Sub.prototype = Object.create(Parent.prototype);
      Object.assign(Sub.prototype, proto);
      (Sub as any).extend = backboneExtend;
      return Sub;
    }
    (AbstractFilter as any).extend = backboneExtend;
    return AbstractFilter;
  },
  {virtual: true}
);

import React from 'react';
import ReactFilterBase from '../../../Resources/public/js/datafilter/filter/ReactFilterBase';

const FakeFilter = (ReactFilterBase as any).extend({
  reactElement() {
    return React.createElement('input', {className: 'fake-filter', name: 'value'});
  },
});

test('render() mounts the subclass reactElement() into this.el', () => {
  const filter = new FakeFilter();

  filter.render();

  expect(filter.el.querySelector('input.fake-filter[name="value"]')).not.toBeNull();
});

test('a base ReactFilterBase renders nothing (reactElement defaults to null)', () => {
  const Empty = (ReactFilterBase as any).extend({});
  const filter = new Empty();

  filter.render();

  expect(filter.el.childElementCount).toBe(0);
});

test('remove() unmounts the React tree AND chains to AbstractFilter.prototype.remove', () => {
  const filter = new FakeFilter();
  filter.render();
  expect(filter.el.querySelector('input.fake-filter')).not.toBeNull();

  filter.remove();

  expect(filter.el.querySelector('input.fake-filter')).toBeNull(); // React unmounted
  expect(filter.removed).toBe(true); // Backbone teardown still ran
});
